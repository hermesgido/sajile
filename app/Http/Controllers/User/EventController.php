<?php

namespace App\Http\Controllers\user;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\EventParticipant;
use App\Models\PostImage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\GatewayCurrency;
use App\Rules\FileTypeValidate;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    public function payment(Post $post)
    {
        if (!auth()->user()) {
            return redirect()->route('user.login');
        }

        $eventJoin = new EventParticipant();
        $pageTitle = 'Event-Credit';
        $exist_join = $eventJoin->where('post_id', $post->id)->where('user_id', auth()->id())->first();
        if ($exist_join) {
            $notify[] = ['error', 'You are already register this event.'];
            return back()->withNotify($notify);
        }

        if ($post->fee == 0 || $post->fee == null) {
            $eventJoin->post_id = $post->id;
            $eventJoin->user_id = auth()->id();
            $eventJoin->status = 1;
            $eventJoin->save();
            $notify[] = ['success', 'Job Apply successfully'];
            return back()->withNotify($notify);
        }

        $eventJoin->post_id = $post->id;
        $eventJoin->user_id = auth()->id();
        $eventJoin->status = 0;
        $eventJoin->save();
        
        $user = User::where('id', auth()->user()->id)->with('posts', 'posts.comments')->first();
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', 1);
        })->with('method')->orderby('method_code')->get();
        return view($this->activeTemplate . 'user.payment.event', compact('gatewayCurrency', 'pageTitle', 'user', 'gatewayCurrency', 'post'));
    }


    public function lists(Request $request)
    {
        $pageTitle = 'My-Events';
        $emptyMessage = 'No event found';
        $events = Post::where('user_id', auth()->id())->where('type', 'event')->orderBy('id', 'desc')->paginate(getPaginate());
        if ($request->search) {
            $events = Post::where('title', 'like', "%$request->search%")->orderBy('id', 'desc')->paginate(getPaginate());
        }
        $user = User::where('id', auth()->user()->id)->with('posts.comments')->first();
        return view($this->activeTemplate . 'user.my-events.event-list', compact('pageTitle', 'events', 'emptyMessage', 'user'));
    }

    public function event_participant(Request $request, $id)
    {
        $pageTitle = 'Event Participant';
        $emptyMessage = 'No participant found';
        $participants = EventParticipant::with('user', 'post')->where('post_id', $id)->orderBy('id', 'desc')->paginate(getPaginate());
        $user = User::where('id', auth()->id())->with('posts.comments')->first();
        if ($request->search) {
            $candidates = EventParticipant::where('post_id', $id)->with(['user', 'post'])->whereHas('user', function ($q) use ($request) {
                $q->where('firstname', 'like', "%$request->search%")->orWhere('lastname', 'like', "%$request->search%");
            })->orderBy('id', 'desc')->paginate(getPaginate());
        }

        return view($this->activeTemplate . 'user.my-events.event-participant', compact('pageTitle', 'participants', 'emptyMessage', 'user'));
    }

    public function postStatus(Request $request)
    {
        $data = Post::where('id', $request->id)->first();
        $data->status = $this->statusCheck($data);
        return response()->json(
            $data = [
                'status' => "success",
                'message' => "Event status updated"
            ],
        );
    }

    private function statusCheck($data)
    {
        if ($data->status === 1) {
            $data->status = 0;
            $data->save();
        } elseif ($data->status === 0) {
            $data->status = 1;
            $data->save();
        }
        return $data;
    }
}
