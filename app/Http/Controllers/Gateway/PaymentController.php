<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Post;
use App\Models\User;
use App\Models\Deposit;
use App\Models\PricePlan;
use App\Lib\FormProcessor;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\GatewayCurrency;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use App\Models\EventParticipant;

class PaymentController extends Controller
{

    public function deposit()
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', 1);
        })->with('method')->orderby('method_code')->get();
        $pageTitle = 'Deposit Methods';
        $user = User::where('id', auth()->user()->id)->with('posts.comments')->first();
        return view($this->activeTemplate . 'user.payment.deposit', compact('gatewayCurrency', 'pageTitle', 'user'));
    }

    public function depositInsert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'method_code' => 'required',
            'currency' => 'required',
        ]);
        if ($request->price_plan_id != 0) {
            $request->validate([
                'price_plan_id' => 'required|numeric',
            ]);
        } elseif ($request->credit != 0) {
            $request->validate([
                'credit' => 'required|numeric',
            ]);
        } else {
            $request->validate([
                'post_id' => 'required|numeric',
            ]);
        }
        $user = auth()->user();
        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', 1);
        })->where('method_code', $request->method_code)->where('currency', $request->currency)->first();

        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        if ($gate->min_amount > $request->amount || $gate->max_amount < $request->amount) {
            $notify[] = ['error', 'Please follow deposit limit'];
            return back()->withNotify($notify);
        }

        // ---------------------------when customer buy price plan---------------------------
        if ($request->price_plan_id && $request->price_plan_id != 0 && $request->price_plan_id != null) {
            $price_plan = PricePlan::where('id', $request->price_plan_id)->first();
            if (!$price_plan) {
                $notify[] = ['error', 'Your plan is not valid'];
                return back()->withNotify($notify);
            }

            $charge = $gate->fixed_charge + ($price_plan->price * $gate->percent_charge / 100);
            $payable = $price_plan->price + $charge;
            $final_amo = $payable * $gate->rate;
        } elseif ($request->credit && $request->credit != 0 && $request->credit != null) {
            // -------------------------------when customer buy refill plan-------------------------------
            $requestAmount = $request->credit * gs()->per_credit_price;
            $charge = $gate->fixed_charge + ($requestAmount * $gate->percent_charge / 100);
            $payable = $requestAmount + $charge;
            $final_amo = $payable * $gate->rate;
        } else {
            // -------------------------------when customer buy event-------------------------------
            $post = Post::where('id', $request->post_id)->first();
            if (!$post) {
                $notify[] = ['error', 'Your Post is not valid'];
                return back()->withNotify($notify);
            }
            $charge = $gate->fixed_charge + ($post->fee * $gate->percent_charge / 100);
            $payable = $post->fee + $charge;
            $final_amo = $payable * $gate->rate;
        }

        $data = new Deposit();
        $data->user_id = $user->id;
        $data->price_plan_id = $price_plan->id ?? 0;
        $data->post_id = $post->id ?? 0;
        $data->method_code = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount = $requestAmount ?? $request->amount;
        $data->credit = $request->credit ?? ($price_plan->credit ?? 0);
        $data->charge = $charge;
        $data->rate = $gate->rate;
        $data->final_amo = $final_amo;
        $data->btc_amo = 0;
        $data->btc_wallet = "";
        $data->trx = getTrx();
        $data->try = 0;
        $data->status = 0;
        $data->save();
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }

    public function appDepositConfirm($hash)
    {
        try {
            $id = decrypt($hash);
        } catch (\Exception $ex) {
            return "Sorry, invalid URL.";
        }
        $data = Deposit::where('id', $id)->where('status', 0)->orderBy('id', 'DESC')->firstOrFail();
        $user = User::findOrFail($data->user_id);
        auth()->login($user);
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }

    public function depositConfirm()
    {
        $track = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status', 0)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();
        $user = User::where('id', auth()->user()->id)->with('posts.comments')->first();

        if ($deposit->method_code >= 1000) {
            return to_route('user.deposit.manual.confirm');
        }

        $dirName = $deposit->gateway->alias;
        $new = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);


        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return to_route(gatewayRedirectUrl())->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }
        // for Stripe V3
        if (@$data->session) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }
        $pageTitle = 'Payment Confirm';
        return view($this->activeTemplate . $data->view, compact('data', 'pageTitle', 'deposit','user'));
    }

    public static function userDataUpdate($deposit, $isManual = null)
    {
        if ($deposit->status == 0 || $deposit->status == 2) {
            $deposit->status = 1;
            $deposit->save();

            if (($deposit->post_id == 0 && $deposit->price_plan_id == 0) || ($deposit->post_id == 0 && $deposit->price_plan_id == 1)) {
                // That means this deposit refill plan or price plan
                $user = User::find($deposit->user_id);
                $user->credit += $deposit->credit;
                $user->save();
            }else{
                // That means this deposit event register 
                $user = User::find($deposit->user_id);
                $user->balance += $deposit->amount;
                $user->save();

                $event_participant = EventParticipant::where('post_id',$deposit->post_id)->where('user_id',$deposit->user_id)->first();
                $event_participant->status = 1;
                $event_participant->save();
            }

            $transaction = new Transaction();
            $transaction->user_id = $deposit->user_id;
            $transaction->amount = $deposit->amount;
            $transaction->post_balance = $user->balance;
            $transaction->post_credit = $deposit->credit;
            $transaction->charge = $deposit->charge;
            $transaction->trx_type = '+';
            $transaction->details = 'Deposit Via ' . $deposit->gatewayCurrency()->name;
            $transaction->trx = $deposit->trx;
            $transaction->remark = 'deposit';
            $transaction->save();

            if (!$isManual) {
                $adminNotification = new AdminNotification();
                $adminNotification->user_id = $user->id;
                $adminNotification->title = 'Deposit successful via ' . $deposit->gatewayCurrency()->name;
                $adminNotification->click_url = urlPath('admin.deposit.successful');
                $adminNotification->save();
            }

            notify($user, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                'method_name' => $deposit->gatewayCurrency()->name,
                'method_currency' => $deposit->method_currency,
                'method_amount' => showAmount($deposit->final_amo),
                'amount' => showAmount($deposit->amount),
                'charge' => showAmount($deposit->charge),
                'rate' => showAmount($deposit->rate),
                'trx' => $deposit->trx,
                'post_balance' => showAmount($user->balance)
            ]);
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data = Deposit::with(['gateway', 'price_plan', 'post'])->where('status', 0)->where('trx', $track)->first();
        $user = User::where('id', auth()->user()->id)->with('posts.comments')->first();
        if (!$data) {
            return to_route(gatewayRedirectUrl());
        }
        if ($data->method_code > 999) {
            $pageTitle = 'Deposit Confirm';
            $method = $data->gatewayCurrency();
            $gateway = $method->method;
            return view($this->activeTemplate . 'user.payment.manual', compact('data', 'pageTitle', 'method', 'gateway', 'user'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data = Deposit::with(['gateway'])->where('status', 0)->where('trx', $track)->first();
        if (!$data) {
            return to_route(gatewayRedirectUrl());
        }

        $gatewayCurrency = $data->gatewayCurrency();
        $gateway = $gatewayCurrency->method;
        $formData = $gateway->form->form_data;

        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);

        $data->detail = $userData;
        $data->status = 2; // pending
        $data->save();


        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $data->user->id;
        $adminNotification->title = 'Deposit request from ' . $data->user->username;
        $adminNotification->click_url = urlPath('admin.deposit.details', $data->id);
        $adminNotification->save();

        notify($data->user, 'DEPOSIT_REQUEST', [
            'method_name' => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount' => showAmount($data->final_amo),
            'amount' => showAmount($data->amount),
            'charge' => showAmount($data->charge),
            'rate' => showAmount($data->rate),
            'trx' => $data->trx
        ]);

        $notify[] = ['success', 'You have deposit request has been taken'];
        return to_route('user.deposit.history')->withNotify($notify);
    }
}
