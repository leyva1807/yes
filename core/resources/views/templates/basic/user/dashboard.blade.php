@extends($activeTemplate . 'layouts.master')
@section('content')
    @php
        $kycInstruction = getContent('kyc_instruction_user.content', true);
    @endphp
    <div class="section section--xl">
        @if (auth()->user()->kv != 1)
            <div class="section__head">
                <div class="container">
                    <div class="row">
                        @if (auth()->user()->kv == 0)
                            <div class="col-12">
                                <div class="alert alert-info mb-0" role="alert">
                                    <h5 class="alert-heading m-0">@lang('KYC Verification Required')</h5>
                                    <hr>
                                    <p class="mb-0"> {{ __($kycInstruction->data_values->verification_instruction) }} <a href="{{ route('user.kyc.form') }}">@lang('Click Here to Verify')</a></p>
                                </div>
                            </div>
                        @elseif(auth()->user()->kv == 2)
                            <div class="col-12">
                                <div class="alert alert-warning mb-0" role="alert">
                                    <h5 class="alert-heading m-0">@lang('KYC Verification pending')</h5>
                                    <hr>
                                    <p class="mb-0"> {{ __($kycInstruction->data_values->pending_instruction) }} <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a></p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="section__head">
            <div class="container">
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <div class="dashboard-card flex-grow-1">
                        <div class="user align-items-center justify-content-center">
                            <div class="icon icon--lg icon--circle">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="user__content">
                                <p class="xl-text mb-0">@lang('Refunded Wallet Balance')</p>
                                <div class="text mt-2 mb-0">
                                    {{ $general->cur_sym }}{{ showAmount($widget['balance']) }}
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="dashboard-card flex-grow-1">
                        <div class="user align-items-center justify-content-center">
                            <div class="icon icon--lg icon--circle">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="user__content">
                                <p class="xl-text mb-0">@lang('Send Money Completed')</p>
                                <div class="text  mt-2 mb-0">
                                    {{ $general->cur_sym }}{{ showAmount($widget['send_money_amount']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-card flex-grow-1">
                        <div class="user align-items-center justify-content-center">
                            <div class="icon icon--lg icon--circle">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <div class="user__content">
                                <p class="xl-text mb-0">@lang('Send Money Pending')</p>
                                <div class="text  mt-2 mb-0">
                                    {{ $general->cur_sym }}{{ showAmount($widget['send_money_pending']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-card flex-grow-1">
                        <div class="user align-items-center justify-content-center">
                            <div class="icon icon--lg icon--circle">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="user__content">
                                <p class="xl-text mb-0">@lang('Send Money Initiated')</p>
                                <div class="text">
                                    {{ $general->cur_sym }}{{ showAmount($widget['send_money_initiated']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-card flex-grow-1">
                        <div class="user align-items-center justify-content-center">
                            <div class="icon icon--lg icon--circle">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <div class="user__content">
                                <p class="xl-text mb-0">@lang('Pending Payment')</p>
                                <div class="text">
                                    {{ $general->cur_sym }}{{ showAmount($widget['payment_pending']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-card flex-grow-1">
                        <div class="user align-items-center justify-content-center">
                            <div class="icon icon--lg icon--circle">
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="user__content">
                                <p class="xl-text mb-0">@lang('Rejected Payment')</p>
                                <div class="text">
                                    {{ $general->cur_sym }}{{ showAmount($widget['payment_rejected']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row g-lg-3">
                <div class="col-12">
                    <div class="custom--table__header">
                        <h5 class="text-lg-start m-0 text-center">@lang('Recent Send Money Log')</h5>
                    </div>
                </div>
                <div class="col-12">
                    <div class="table-responsive--md">

                        @include($activeTemplate . 'partials.send_money_table', ['transfers' => $transfers, 'hasBtn' => false])
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- feedback MODAL --}}
    <div class="modal custom--modal fade" id="feedbackModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Feedback')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal" type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <span class="admin_feedback"></span>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        (function($) {
            "use strict";
            $('.feedbackBtn').on('click', function() {
                var modal = $('#feedbackModal');
                modal.find('.admin_feedback').text($(this).data('admin_feedback'));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush

@push('style-lib')
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600&display=swap" rel="stylesheet">
@endpush
@push('style')
    <style>
        .dashboard-card .user__content h4 {
            font-family: "rajdhani", sans-serif;
            font-weight: 500;
        }
    </style>
@endpush
