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
                        <h5 class="title mb-3">@lang('Flutterwave')</h5>
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
                        <button type="button" class="btn btn--base w-100 mt-3" id="btn-confirm" onClick="payWithRave()">@lang('Pay Now')</button>
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






@push('script')
    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script>
        "use strict"
        var btn = document.querySelector("#btn-confirm");
        btn.setAttribute("type", "button");
        const API_publicKey = "{{$data->API_publicKey}}";
        function payWithRave() {
            var x = getpaidSetup({
                PBFPubKey: API_publicKey,
                customer_email: "{{$data->customer_email}}",
                amount: "{{$data->amount }}",
                customer_phone: "{{$data->customer_phone}}",
                currency: "{{$data->currency}}",
                txref: "{{$data->txref}}",
                onclose: function () {
                },
                callback: function (response) {
                    var txref = response.tx.txRef;
                    var status = response.tx.status;
                    var chargeResponse = response.tx.chargeResponseCode;
                    if (chargeResponse == "00" || chargeResponse == "0") {
                        window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
                    } else {
                        window.location = '{{ url('ipn/flutterwave') }}/' + txref + '/' + status;
                    }
                        // x.close(); // use this to close the modal immediately after payment.
                    }
                });
        }
    </script>
@endpush
