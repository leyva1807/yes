@extends('admin.layouts.app')

@section('panel')
    <div class="row gy-3 justify-content-center">
        @if ($deposit->sendMoney && $deposit->status != Status::PAYMENT_INITIATE)
            <div class="col-12">
                <div class="alert alert-info p-3">
                    <p class="f-size--24">
                        <a href="{{ route('admin.users.detail', $deposit->user_id) }}" class="f-size--24">{{ @$deposit->user->username }}</a> @lang('have paid') <span class="fw-bold text--primary">{{ showAmount($deposit->amount) }} {{ __($general->cur_text) }}</span>
                        @lang('to send') <span class="fw-bold">{{ showAmount($deposit->sendMoney->sending_amount) }} {{ $deposit->sendMoney->sending_currency }} </span>
                        @lang('to') {{ @$deposit->sendMoney->recipientCountry->name }} @lang('from') {{ @$deposit->sendMoney->sendingCountry->name }}. @lang('The recipient will receive') <span class="fw-bold">{{ showAmount($deposit->sendMoney->recipient_amount) }} {{ $deposit->sendMoney->recipient_currency }}</span>
                    </p>
                </div>
            </div>
        @endif
        <div class="col-xl-4 col-md-6">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="mb-20 text-muted">{{ __(ucfirst($type)) }} @lang('Via') {{ __(@$deposit->gateway->name) }}</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Date')
                            <span class="fw-bold">{{ showDateTime($deposit->created_at) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Transaction Number')
                            <span class="fw-bold">{{ $deposit->trx }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            @if ($deposit->user_id)
                                <span class="fw-bold">
                                    <a href="{{ route('admin.users.detail', $deposit->user_id) }}">{{ @$deposit->user->username }}</a>
                                </span>
                            @else
                                <span class="fw-bold">
                                    <a href="{{ route('admin.agents.detail', $deposit->agent_id) }}">{{ @$deposit->agent->username }}</a>
                                </span>
                            @endif
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Method')
                            <span class="fw-bold">{{ __(@$deposit->gateway->name) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Amount')
                            <span class="fw-bold">{{ showAmount($deposit->amount) }} {{ __($general->cur_text) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Charge')
                            <span class="fw-bold">{{ showAmount($deposit->charge) }} {{ __($general->cur_text) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('After Charge')
                            <span class="fw-bold">{{ showAmount($deposit->amount + $deposit->charge) }} {{ __($general->cur_text) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Rate')
                            <span class="fw-bold">1 {{ __($general->cur_text) }} = {{ showAmount($deposit->rate) }} {{ __($deposit->baseCurrency) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Payable')
                            <span class="fw-bold">{{ showAmount($deposit->final_amo) }} {{ __($deposit->method_currency) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Status')
                            @php echo $deposit->statusBadge @endphp
                        </li>

                        @if ($deposit->admin_feedback)
                            <li class="list-group-item">
                                <strong>@lang('Admin Response')</strong>
                                <br>
                                <p>{{ __($deposit->admin_feedback) }}</p>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        @if ($details || $deposit->status == Status::PAYMENT_PENDING)
            <div class="col-xl-8 col-md-6">
                <div class="card b-radius--10 overflow-hidden box--shadow1">
                    <div class="card-header">
                        <h5 class="card-title">
                            @if ($type == 'payment')
                                @lang('User Payment Information')
                            @else
                                @lang('Agent Deposit Information')
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($details != null)
                            @foreach (json_decode($details) as $val)
                                @if ($deposit->method_code >= 1000)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>{{ __($val->name) }}</h6>
                                            @if ($val->type == 'checkbox')
                                                {{ implode(',', $val->value) }}
                                            @elseif($val->type == 'file')
                                                @if ($val->value)
                                                    <a href="{{ route('admin.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}" class="me-3"><i class="fa fa-file"></i> @lang('Attachment') </a>
                                                @else
                                                    @lang('No File')
                                                @endif
                                            @else
                                                <p>{{ __($val->value) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            @if ($deposit->method_code < 1000)
                                @include('admin.deposit.gateway_data', ['details' => json_decode($details)])
                            @endif
                        @endif
                        @if ($deposit->status == Status::PAYMENT_PENDING)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <button class="btn btn-outline--success btn-sm ms-1 confirmationBtn" data-action="{{ route('admin.deposit.approve', $deposit->id) }}" data-question="@lang('Are you sure to approve this transaction?')"><i class="las la-check-double"></i>
                                        @lang('Approve')
                                    </button>

                                    <button class="btn btn-outline--danger btn-sm ms-1 rejectBtn" data-id="{{ $deposit->id }}"><i class="las la-ban"></i> @lang('Reject')
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- REJECT MODAL --}}
    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if ($type == 'payment')
                            @lang('Reject Payment Confirmation')
                        @else
                            @lang('Reject Deposit Confirmation')
                        @endif
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.deposit.reject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <p>@lang('Are you sure to reject this transaction?')</p>

                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Reason for Rejection')</label>
                            <textarea name="message" maxlength="255" class="form-control" rows="5" required>{{ old('message') }}</textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.rejectBtn').on('click', function() {
                var modal = $('#rejectModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
