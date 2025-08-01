@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section>
    <!-- header -->
    @include('presets.default.components.header')
    @include('presets.default.components.sidenav')
    <!-- body -->
    <div class="body-section">
        <div class="container-fluid">
            <div class="row">
                <!-- left side -->
                @include('presets.default.components.user.sidebar')
                <!-- left side / -->

                {{-- main content --}}
                <div class="col-xl-6 col-lg-6">
                    <div class="row justify-content-center pt-60 gy-4 px-3">
                        <h5 class="title mb-3">@lang('Paystack')</h5>
                        <form action="{{ route('ipn.'.$deposit->gateway->alias) }}" method="POST" class="text-center">
                            @csrf
                            <ul class="list-group text-center">
                                <li class="list-group-item d-flex justify-content-between">
                                    @lang('You have to pay '):
                                    <strong>{{showAmount($deposit->final_amo)}} {{__($deposit->method_currency)}}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    @lang('You will get '):
                                    <strong>{{showAmount($deposit->amount)}}  {{__($general->cur_text)}}</strong>
                                </li>
                            </ul>
                            <button type="button" class="btn btn--base w-100 mt-3" id="btn-confirm">@lang('Pay Now')</button>
                            <script
                                src="//js.paystack.co/v1/inline.js"
                                data-key="{{ $data->key }}"
                                data-email="{{ $data->email }}"
                                data-amount="{{ round($data->amount) }}"
                                data-currency="{{$data->currency}}"
                                data-ref="{{ $data->ref }}"
                                data-custom-button="btn-confirm"
                            >
                            </script>
                        </form>
                    </div>
                </div>
                {{-- main content / --}}

                <!-- right side -->
                <div class="col-lg-3">
                    <aside class="rightside-bar">
                        @include('presets.default.components.user_info')
                        @include('presets.default.components.popular')
                    </aside>
                </div>
                <!-- right side /-->
            </div>
        </div>
    </div>
</section>
@endsection
