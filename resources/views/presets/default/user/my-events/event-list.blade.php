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
                            <div class="col-lg-12 ">
                                <div class="order-wrap mt-3">
                                    <div class="row justify-content-end">
                                        <div class="col-md-3 mb-3">
                                            <form>
                                                <div class="search-box w-100">
                                                    <input type="text" name="search" class="form--control"
                                                        value="{{ request()->search }}" placeholder="@lang('Search...')">
                                                    <button type="submit" class="search-box__button"><i
                                                            class="fas fa-search"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="table-responsive table-wrap">
                                        <table class="table table--responsive--xl">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">@lang('Title')</th>
                                                    <th class="text-center">@lang('Fee')</th>
                                                    <th class="text-center">@lang('Start date')</th>
                                                    <th class="text-center">@lang('End date')</th>
                                                    <th class="text-center">@lang('Status')</th>
                                                    <th>@lang('participant')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($events as $event)
                                                    <tr>
                                                        <td class="text-center" data-label="Title">{{ __($event->title) }}</td>
                                                        <td data-label="fee">{{ __($general->cur_sym) }}{{ showAmount($event->fee) }}
                                                        </td>
                                                        <td data-label="Start date">{{ showDateTime($event->start_date,'d M Y, h:i A') }}</td>

                                                        <td data-label="End date">{{ showDateTime(@$event->end_date,'d M Y, h:i A') }}</td>
                                                        <td data-label="status">
                                                            <label class="switch m-0">
                                                                <input type="checkbox" class="toggle-switch reportStatus"
                                                                    data-id="{{ $event->id }}" name="status"
                                                                    {{ $event->status ? 'checked' : null }}>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </td>
                                                        <td class="text-center" data-label="Details">
                                                            <a href="{{ route('user.events.participant', $event->id) }}"
                                                                class="btn--base outline btn-sm">
                                                                <i class="las la-users fs-5"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-muted text-center" data-label="Details" colspan="100%">{{ __($emptyMessage) }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                        <div class="row justify-content-end mt-3">
                                            <div class="col-md-3 mb-3">
                                                {{ $events->links() }}
                                            </div>
                                        
                                    </div>
                                </div>
                            </div>
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
    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');

                var userData = $(this).data('info');
                var html = '';
                if (userData) {
                    userData.forEach(element => {
                        if (element.type != 'file') {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${element.name}</span>
                                <span">${element.value}</span>
                            </li>`;
                        }
                    });
                }
                modal.find('.userData').html(html);
                if ($(this).data('admin_feedback') != undefined) {
                    var adminFeedback = `
                        <div class="my-3">
                            <strong>@lang('Admin Feedback')</strong>
                            <p>${$(this).data('admin_feedback')}</p>
                        </div>
                    `;
                } else {
                    var adminFeedback = '';
                }

                modal.find('.feedback').html(adminFeedback);


                modal.modal('show');
            });
        })(jQuery);

        $(document).ready(function() {
            "use strict";
            $(".reportStatus").on('click', function() {
                var url = "{{ route('user.events.status') }}";
                var token = '{{ csrf_token() }}';
                var data = {
                    id: $(this).data("id"),
                    _token: token
                }
                $.post(url, data, function(data, status) {
                    if (data.status == "success") {
                        Toast.fire({
                            icon: 'success',
                            title: data.message
                        })
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message
                        })
                    }
                });
            });

        });
    </script>
@endpush
