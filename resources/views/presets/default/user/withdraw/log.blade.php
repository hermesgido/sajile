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
                            <div class="col-lg-12">
                                <div class="order-wrap">
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
                                                    <th>@lang('Gateway')</th>
                                                    <th class="text-center">@lang('Initiated')</th>
                                                    <th class="text-center">@lang('Amount')</th>
                                                    <th class="text-center">@lang('Conversion')</th>
                                                    <th class="text-center">@lang('Status')</th>
                                                    <th>@lang('Action')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($withdraws as $withdraw)
                                                    <tr>
                                                        <td class="text-center" data-label="Gateway">
                                                            {{ __(@$withdraw->method->name) }}</td>
                                                        <td data-label="Initiated">
                                                            {{ showDateTime($withdraw->created_at) }}
                                                        </td>
                                                        <td data-label="Amount">
                                                            <strong title="@lang('Amount with charge')">
                                                                {{ showAmount($withdraw->amount) }}
                                                                {{ __($general->cur_text) }}
                                                            </strong>
                                                      
                                                        </td>
                                                        <td data-label="Conversion">
                                                            <strong>{{ showAmount($withdraw->final_amount) }}
                                                                {{ __($withdraw->currency) }}</strong>
                                                        </td>
                                                        <td data-label="Status"> @php echo $withdraw->statusBadge @endphp</td>
                                                        @php
                                                            $details = $withdraw->withdraw_information != null ? json_encode($withdraw->withdraw_information) : null;
                                                        @endphp
                                                        <td class="text-center" data-label="Action">
                                                            <a href="javascript:void(0)"
                                                            class="btn--base outline p-2 detailBtn"
                                                                data-user_data="{{ json_encode($withdraw->withdraw_information) }}"
                                                                @if ($withdraw->status == 3) data-admin_feedback="{{ $withdraw->admin_feedback }}" @endif>
                                                                <i class="las la-list fs-5"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td data-label="Details" colspan="7" class="text-center">
                                                            {{ __($emptyMessage) }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        {{ $withdraws->links() }}
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

        {{-- APPROVE MODAL --}}
        <div id="detailModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('Details')</h5>
                        <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group userData">

                        </ul>
                        <div class="feedback"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark btn-sm"
                            data-bs-dismiss="modal">@lang('Close')</button>
                    </div>
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
                var userData = $(this).data('user_data');
                var html = ``;
                userData.forEach(element => {
                    if (element.type != 'file') {
                        html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>${element.name}</span>
                            <span">${element.value}</span>
                        </li>`;
                    }
                });
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
    </script>
@endpush