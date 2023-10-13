@extends($activeTemplate . 'layouts.master')
@section('content')
    @php
        if (old()) {
            $sendingAmount = old('sending_amount');
            $recipientAmount = old('recipient_amount');
            $deliveryMethodId = old('delivery_method');
            $sendingCountryId = old('sending_country');
            $recipientCountryId = old('recipient_country');
        }
        
        $user = auth()->user();
    @endphp
    <section class="section section--sm">
        <div class="container">
            <h4 class="text-center">@lang('Send Money Form')</h4>
            <form action="{{ route('user.send.money.save') }}" class="card-body container-fluid register" method="post">
                @csrf
                <div class="row g-4">
                    <div class="col-lg-6 ">
                        <div class="card custom--card h-100">
                            <div class="card-header">
                                <h5 class="card-title text-center">@lang('Sender Information')</h5>
                            </div>
                            <div class="card-body">

                                <div class="exchange-form">
                                    <div class="exchange-form__body p-0">
                                        @include($activeTemplate . 'partials.country_fields', [
                                            'class' => 'mb-3',
                                            'showLimit' => true,
                                        ])

                                        <div class="conversion__rate mb-5">
                                            <div>1 <span class="sending-currency"></span> = </div>
                                            <div class="exchange-rate ms-1"></div>
                                            <div class="recipient-currency ms-1"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="text--accent sm-text d-block fw-md mb-2"
                                                for="source_of_funds">@lang('Source of Funds')</label>
                                            <div class="form--select-light">
                                                <select class="form-select form--select" id="source_of_funds"
                                                    name="source_of_funds" required>
                                                    <option value="">@lang('Select One')</option>
                                                    @foreach ($sources as $source)
                                                        <option @selected(old('source_of_funds') == $source->id) value="{{ $source->id }}">
                                                            {{ __($source->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="text--accent sm-text d-block fw-md mb-2"
                                                for="source_purpose">@lang('Sending Purpose')</label>
                                            <div class="form--select-light">
                                                <select class="form-select form--select" id="source_purpose"
                                                    name="sending_purpose">
                                                    <option value="">@lang('Select One')</option>
                                                    @foreach ($purposes as $purpose)
                                                        <option @selected(old('sending_purpose') == $purpose->id) value="{{ $purpose->id }}">
                                                            {{ __($purpose->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h5 class="">@lang('Payment Via')</h5>
                                    <div class="d-flex flex-wrap gap-3">
                                        <label class="btn-selected__label flex-grow-1" data-value="1" for="walletBtn">
                                            <input class="btn-selected__input walletPayment"
                                                data-balance="{{ auth()->user()->balance }}" id="walletBtn"
                                                name="payment_type" required type="radio" value="1">
                                            <span class="btn-selected btn-selected--primary">
                                                <span class="btn-selected__icon">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ getImage($activeTemplateTrue . 'images/wallet-icon.png') }}">
                                                </span>
                                                <span class="btn-selected__text ">
                                                    @lang('Refunded') @lang('Wallet')
                                                </span>
                                            </span>
                                        </label>
                                        <label class="btn-selected__label flex-grow-1" data-value="2" for="directBtn">
                                            <input class="btn-selected__input directPayment" id="directBtn"
                                                name="payment_type" required type="radio" value="2">
                                            <div class="btn-selected btn-selected--secondary">
                                                <span class="btn-selected__icon">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ getImage($activeTemplateTrue . 'images/credit-card-icon.png') }}">
                                                </span>
                                                <span class="btn-selected__text ">
                                                    @lang('Direct Payment')
                                                </span>
                                            </div>
                                        </label>
                                    </div>
                                    <small class="text--danger insufficientBalanceError d-none">@lang('You don\'t have sufficient balance. Your current balance is')
                                        {{ showAmount(auth()->user()->balance) }} {{ $general->cur_text }}</small>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card custom--card">
                            <div class="card-header">
                                <h5 class="card-title text-center">@lang('Recipient Information')</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2"
                                        for="">@lang('Existings Recipient')</label>
                                    <div class="form--select-light">
                                        <select class="form-select form--select existings-recipient">
                                            <option value="">@lang('Select One')</option>
                                            @foreach ($recipients as $recipient)
                                                <option  data-recipient="{{ $recipient }}">{{ $recipient->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2"
                                        for="recipient_name">@lang('Recipient Name')</label>
                                    <input class="form-control form--control" id="recipient_name" name="recipient[name]"
                                        required type="text" value="{{ old('recipient')['name'] ?? null }}">
                                </div>
                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2"
                                        for="recipient_mobile">@lang('Recipient Mobile No.')</label>
                                    <div class="input-group">
                                        <span class="input-group-text recipient-dial-code"></span>
                                        <input class="form-control form--control checkRecipient" id="recipient_mobile"
                                            name="recipient[mobile]" required type="number"
                                            value="{{ old('recipient')['mobile'] ?? null }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2"
                                        for="recipient_email">@lang('Recipient Email')</label>
                                    <input class="form-control form--control checkRecipient" id="recipient_email"
                                        name="recipient[email]" required type="email"
                                        value="{{ old('recipient')['email'] ?? null }}">
                                </div>
                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2"
                                        for="recipient_address">@lang('Recipient Address')</label>
                                    <input class="form-control form--control-textarea" id="recipient_address"
                                        name="recipient[address]" value="{{ old('recipient')['address'] ?? null }}">
                                </div>

                                <div class="mb-3">
                                    <label class="text--accent sm-text d-block fw-md mb-2"
                                        for="deliveryMethod">@lang('Delivery Methods')</label>
                                    <div class="form--select-light">
                                        <select class="form-select form--select" id="deliveryMethod"
                                            name="delivery_method" required>
                                            <option value="">@lang('Select One')</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3 services-div"></div>
                                <div class="mb-3 mt-4 d-none formData"></div>
                                <div class="mb-3 mt-4" id="save_recipient">

                                    <input type="checkbox" @checked(old('save_recipient')) name="save_recipient">
                                    <label for="save_recipient">@lang('Save Recipient')</label>
                                </div>

                                <div class="mb-3 mt-4">
                                    <ul class="list list--column payment-table">
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="d-block t-heading-font heading-clr sm-text">
                                                    @lang('Sending Amount')
                                                </span>
                                                <h5 class="fw-md heading-clr t-heading-font sm-text m-0">
                                                    <span class="sending-amount-total"></span>
                                                    <span class="sending-currency"></span>
                                                </h5>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="d-block sm-text">
                                                    @lang('Total Charge')
                                                </span>
                                                <h5 class="fw-md heading-clr t-heading-font sm-text m-0">
                                                    <span class="charge-amount-text"></span>
                                                    <span class="sending-currency"></span>
                                                </h5>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="d-block t-heading-font heading-clr sm-text">
                                                    @lang('Final Amount')
                                                </span>
                                                <h5 class="sm-text m-0">
                                                    <span class="final-amount-text"></span>
                                                    <span class="sending-currency"></span>
                                                </h5>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="d-block sm-text">
                                                    @lang('Payable In '){{ __($general->cur_text) }}
                                                </span>
                                                <h5 class="text--base sm-text m-0">
                                                    <span class="base-amount-text"></span>
                                                    <span>{{ __($general->cur_text) }}</span>
                                                </h5>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <button class="btn btn--base btn--xl w-100 formSubmitButton"
                                    type="submit">@lang('Continue')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <div class="modal custom--modal fade" id="limitModal" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Send Money Limit')</h5>
                    <button aria-label="Close" class="close btn btn--danger btn-sm close-button" data-bs-dismiss="modal"
                        type="button">
                        <i aria-hidden="true" class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list list--column payment-table" id="transfer-limit">
                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">
                                    @lang('Per Transfer')
                                </span>
                                <h6 class="fw-md heading-clr t-heading-font sm-text m-0">
                                    <span class="send_money_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h6>
                            </div>
                        </li>

                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">
                                    @lang('Daily Limit')
                                </span>
                                <h5 class="sm-text m-0">
                                    <span class="daily_send_money_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h5>
                            </div>
                        </li>

                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">
                                    @lang('Available for Today')
                                </span>
                                <h5 class="sm-text m-0">
                                    <span class="today_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h5>
                            </div>
                        </li>

                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">
                                    @lang('Monthly Limit')
                                </span>
                                <h5 class="sm-text m-0">
                                    <span class="monthly_send_money_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h5>
                            </div>
                        </li>

                        <li>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="d-block t-heading-font heading-clr sm-text">@lang('Available for This Month')</span>
                                <h5 class="sm-text m-0">
                                    <span class="this_month_limit">0</span>
                                    <span class="sending-currency"></span>
                                </h5>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        "use strict";
        let agentStatus = `{{ $general->agent_module }}`;
        let agent = `@lang('Agent')`;
        let agentFixedCharge = `{{ $general->agent_charges->fixed_charge ?? 0 }}`;
        let agentPercentCharge = `{{ $general->agent_charges->percent_charge ?? 0 }}`;

        let sendLimit = `{{ $general->user_send_money_limit }}`;
        let sendingAmount = `{{ $sendingAmount }}`;
        let recipientAmount = `{{ $recipientAmount }}`;
        let deliveryMethodId = `{{ $deliveryMethodId }}`;
        let recipientCountryId = `{{ $recipientCountryId }}`;
        let sendingCountryId = `{{ $sendingCountryId }}`;

        if (deliveryMethodId) {
            deliveryMethodId *= 1;
        }

        let defaultSelectOption = `@lang('Select One')`;
        let serviceStatus = true;
        let serviceURL = "{{ route('services') }}";
        let serviceLabel = `@lang('Service')`;
        let isAgent = false;
    </script>

    <script src="{{ asset('assets/global/js/sendMoney.js') }}"></script>
<script>
        (function($) {
            "use strict";

            $(document).on('change', '.countryServices', function() {
                let serviceId = $(this).val();
                if (serviceId) {
                    let data = {
                        service_id: serviceId
                    }

                    $.get("{{ route('service.form') }}", data,
                        function(data, textStatus, jqXHR) {
                            if (data.success && data.html.length) {
                                $('.formData').html(data.html);
                                $('.formData').find('label').addClass(
                                    'text--accent sm-text d-block fw-md mb-2');
                                $('.formData').removeClass('d-none');
                            } else {
                                $('.formData').html('');
                                $('.formData').addClass('d-none');
                            }
                        }
                    );
                } else {
                    $('.formData').empty();
                }
            });

            $('.walletPayment').on('click', function() {
                checkBalance();
            });

            let availableForToday = 0;
            let availableForThisMonth = 0;
            let limitPerSendMoney = 0;
            let dailyLimit = 0;
            let monthlyLimit = 0;

            $('.country-picker').on('change', function() {
                let general = @json($general);
                let user = @json($user);


                let todaySendMoneyInBaseCur = @json($todaySendMoney);
                let thisMonthSendMoneyInBase = @json($thisMonthSendMoney);
                let sender = $('[name=sending_country]');
                let baseToSenderCurrency = parseFloat(sender.find(':selected').data('rate'));


                if (user.daily_send_money_limit != 0) {
                    dailyLimit = parseFloat(user.daily_send_money_limit * baseToSenderCurrency).toFixed(2);

                } else {

                    dailyLimit = parseFloat(general.user_daily_send_money_limit * baseToSenderCurrency).toFixed(
                        2);
                }

                if (user.monthly_send_money_limit != 0) {
                    monthlyLimit = parseFloat(user.monthly_send_money_limit * baseToSenderCurrency).toFixed(2);

                } else {

                    monthlyLimit = parseFloat(general.user_monthly_send_money_limit * baseToSenderCurrency)
                        .toFixed(2);
                }

                if (user.per_send_money_limit != 0) {
                    limitPerSendMoney = parseFloat(user.per_send_money_limit * baseToSenderCurrency).toFixed(2);

                } else {
                    limitPerSendMoney = parseFloat(general.user_send_money_limit * baseToSenderCurrency)
                        .toFixed(2);

                }

                availableForToday = parseFloat(dailyLimit - todaySendMoneyInBaseCur * baseToSenderCurrency)
                    .toFixed(2);
                availableForToday = parseFloat(availableForToday > 0 ? availableForToday : 0).toFixed(2);
                availableForThisMonth = parseFloat(monthlyLimit - thisMonthSendMoneyInBase *
                    baseToSenderCurrency).toFixed(2);
                availableForThisMonth = parseFloat(availableForThisMonth > 0 ? availableForThisMonth : 0)
                    .toFixed(2);

                if (availableForToday > availableForThisMonth) {
                    availableForToday = availableForThisMonth;
                }

                $('.send_money_limit').text(limitPerSendMoney);
                $('.daily_send_money_limit').text(dailyLimit);
                $('.monthly_send_money_limit').text(monthlyLimit);
                $('.today_limit').text(availableForToday);
                $('.this_month_limit').text(availableForThisMonth);
            }).change();

            $(document).on('input', '.sending-amount, .recipient-amount', function() {
                let sendingAmount = parseFloat($('[name=sending_amount]').val());
                if (sendingAmount > limitPerSendMoney || sendingAmount > availableForToday || sendingAmount >
                    availableForThisMonth) {
                    $('.limitMessage').removeClass('d-none')
                    $('.formSubmitButton').attr('disabled', true);
                } else {
                    $('.limitMessage').addClass('d-none')
                    $('.formSubmitButton').attr('disabled', false);
                }

                if ($('[name=payment_type]:checked').val() == 1) {
                    checkBalance();
                }
            });

            function checkBalance() {
                var balance = $('.walletPayment').data('balance');
                var finalAmount = parseInt($('.base-amount-text').text());
                if (finalAmount > balance) {
                    $('.insufficientBalanceError').removeClass('d-none').fadeIn();
                    $('.formSubmitButton').attr('disabled', true);
                } else {
                    $('.insufficientBalanceError').fadeOut().addClass('d-none');
                    if ($('.limitMessage').hasClass('d-none')) {
                        $('.formSubmitButton').attr('disabled', false);
                    }
                }
            }

            $('.directPayment').on('click', function() {
                $('.insufficientBalanceError').fadeOut().addClass('d-none');
                if ($('.limitMessage').hasClass('d-none')) {
                    $('.formSubmitButton').attr('disabled', false);
                }
            });

            $('.formSubmitButton').on('click', function() {
                var paymentType = $('[name=payment_type]:checked').val();
                if (!paymentType) {
                    notify('error', 'Please select a payment type')
                }
            });

            $('.showLimit').on('click', function() {
                $('#limitModal').modal('show');
            });

            @if (old('payment_type') == 1)
                $('.walletPayment').click();
            @endif

            @if (old('payment_type') == 2)
                $('.directPayment').click();
            @endif

        

            $('.existings-recipient').on('change', function() {

                $('#save_recipient').addClass('d-none');

                let recipient = $(this).find(':selected').data('recipient')
                $('#recipient_name').val(recipient.name);
                $('#recipient_mobile').val(recipient.mobile);
                $('#recipient_email').val(recipient.email);
                $('#recipient_address').val(recipient.address);

                notify('success', 'Previous recipient data found.')

            });

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .exchange-form {
            box-shadow: none;
        }

        .select2-container--default .select2-results__option[aria-disabled=true] {
            display: none;
        }

        .reverseCountryBtn i {
            transform: rotate(90deg);
            font-size: 20px;
        }

        .conversion__rate {
            display: flex;
            justify-content: center;
            font-size: 27px;
            font-weight: 500;
        }
    </style>
@endpush
