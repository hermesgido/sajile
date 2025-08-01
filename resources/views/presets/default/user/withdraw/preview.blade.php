@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section>
        <!-- header -->
        @include('presets.default.components.header')
        @include('presets.default.components.sidenav')
        <!-- body -->
        <div class="body-section">
            <div class="container-fluid">
                <div class="row m-0">
                    <!-- left side -->
                    @include('presets.default.components.user.sidebar')
                    <!-- left side / -->
                    {{-- main content --}}
                    <div class="col-xl-6 col-lg-6">
                        <div class="row pt-80 justify-content-center gy-4 px-3">
                            <h5 class="title mb-3">@lang('Withdraw Via') {{ $withdraw->method->name }}</h5>
                            <form action="{{ route('user.withdraw.submit') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    @php
                                        echo $withdraw->method->description;
                                    @endphp
                                </div>
                                <x-custom-form identifier="id"
                                    identifierValue="{{ $withdraw->method->form_id }}"></x-custom-form>
                                @if (auth()->user()->ts)
                                    <div class="form-group">
                                        <label>@lang('Google Authenticator Code')</label>
                                        <input type="text" name="authenticator_code" class="form-control form--control"
                                            required>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <button type="submit" class="btn btn--base w-100">@lang('Save')</button>
                                </div>
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
