@extends('admin.layouts.app')
@section('panel')
    <form action="" method="POST">
        @csrf
        <div class="row gy-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">@lang('Site Setting')</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Site Title')</label>
                                    <input class="form-control" name="site_name" required type="text" value="{{ $general->site_name }}">
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Currency')</label>
                                    <input class="form-control" name="cur_text" required type="text" value="{{ $general->cur_text }}">
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Currency Symbol')</label>
                                    <input class="form-control" name="cur_sym" required type="text" value="{{ $general->cur_sym }}">
                                </div>
                            </div>

                            <div class="form-group col-md-4 col-sm-6">
                                <label> @lang('Timezone')</label>
                                <div class="select2-parent">
                                    <select class="select2-basic" name="timezone">
                                        @foreach ($timezones as $timezone)
                                            <option value="'{{ @$timezone }}'">{{ __($timezone) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label> @lang('Site Base Color')</label>
                                <div class="input-group">
                                    <span class="input-group-text p-0 border-0">
                                        <input class="form-control colorPicker" type='text' value="{{ $general->base_color }}" />
                                    </span>
                                    <input class="form-control colorCode" name="base_color" type="text" value="{{ $general->base_color }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">@lang('User Send Money Setting')</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Limit Per Send Money') <i class="la la-info-circle text--info" title="@lang('The amount user can send in each send money transaction.')"></i></label>
                                    <div class="input-group">
                                        <input class="form-control" min="0" name="user_send_money_limit" step="any" type="number" value="{{ getAmount($general->user_send_money_limit) }}">
                                        <span class="input-group-text">{{ $general->cur_text }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Daily Send Money Limit') <i class="la la-info-circle text--info" title="@lang('The amount user can send on a calender date.')"></i></label>
                                    <div class="input-group">
                                        <input class="form-control" min="0" name="user_daily_send_money_limit" step="any" type="number" value="{{ getAmount($general->user_daily_send_money_limit) }}">
                                        <span class="input-group-text">{{ $general->cur_text }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Monthly Send Money Limit') <i class="la la-info-circle text--info" title="@lang('The amount user can send on a calender month.')"></i></label>
                                    <div class="input-group">
                                        <input class="form-control" min="0" name="user_monthly_send_money_limit" step="any" type="number" value="{{ getAmount($general->user_monthly_send_money_limit) }}">
                                        <span class="input-group-text">{{ $general->cur_text }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">@lang('Agent Setting')</h5>
                    </div>

                    <div class="card-body">
                        @if ($general->agent_module)
                            <div class="row">

                                <div class="col-xl-4 col-lg-6 col-sm-6">
                                    <div class="form-group ">
                                        <label>@lang('Limit Per Send Money') <i class="la la-info-circle text--info" title="@lang('The amount agent can send in each send money transaction.')"></i></label>
                                        <div class="input-group">
                                            <input class="form-control" min="0" name="agent_send_money_limit" step="any" type="number" value="{{ getAmount($general->agent_send_money_limit) }}">
                                            <span class="input-group-text">{{ $general->cur_text }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-sm-6">
                                    <div class="form-group ">
                                        <label>@lang('Daily Send Money Limit') <i class="la la-info-circle text--info" title="@lang('The amount agent can send on a calender date.')"></i></label>
                                        <div class="input-group">
                                            <input class="form-control" min="0" name="agent_daily_send_money_limit" step="any" type="number" value="{{ getAmount($general->agent_daily_send_money_limit) }}">
                                            <span class="input-group-text">{{ $general->cur_text }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-sm-6">
                                    <div class="form-group ">
                                        <label>@lang('Monthly Send Money Limit') <i class="la la-info-circle text--info" title="@lang('The amount agent can send on a calender month.')"></i></label>
                                        <div class="input-group">
                                            <input class="form-control" min="0" name="agent_monthly_send_money_limit" step="any" type="number" value="{{ getAmount($general->agent_monthly_send_money_limit) }}">
                                            <span class="input-group-text">{{ $general->cur_text }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-sm-6">
                                    <div class="form-group ">
                                        <label>@lang('Fixed Charge') <i class="la la-info-circle text--info" title="@lang('The charge will be applied when the user sends money via an agent.')"></i></label>
                                        <div class="input-group">
                                            <input class="form-control " min="0" name="agent_charges[fixed_charge]" step="any" type="number" value="{{ getAmount(@$general->agent_charges->fixed_charge) }}">
                                            <div class="input-group-text cur_text">
                                                {{ $general->cur_text }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-sm-6">
                                    <div class="form-group ">
                                        <label>@lang('Percent Charge') <i class="la la-info-circle text--info" title="@lang('The charge will be applied when the user sends money via an agent.')"></i></label>
                                        <div class="input-group">
                                            <input class="form-control " min="0" name="agent_charges[percent_charge]" step="any" type="number" value="{{ getAmount(@$general->agent_charges->percent_charge) }}">
                                            <div class="input-group-text cur_text">
                                                %
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-sm-6">
                                    <div class="form-group ">
                                        <label>@lang('OTP Validity') </label>
                                        <div class="input-group">
                                            <input class="form-control" name="resent_code_duration" type="text" value="{{ $general->resent_code_duration }}">
                                            <span class="input-group-text">
                                                @lang('Seconds')
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-sm-6">
                                    <div class="form-group ">
                                        <label> @lang('Fixed Commission')</label>
                                        <div class="input-group">
                                            <input class="form-control " min="0" name="agent_fixed_commission" step="any" type="number" value="{{ getAmount($general->agent_fixed_commission) }}">
                                            <div class="input-group-text cur_text">
                                                {{ $general->cur_text }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-sm-6">
                                    <div class="form-group ">
                                        <label>@lang('Percent Commission') </label>
                                        <div class="input-group">
                                            <input class="form-control" min="0" name="agent_percent_commission" step="any" type="number" value="{{ getAmount($general->agent_percent_commission) }}">
                                            <span class="input-group-text">
                                                %
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">@lang('Referral Setting')</h5>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Referral Commission')</label>
                                    <div class="input-group">
                                        <input class="form-control" min="0" name="referral_commission" step="any" type="number" value="{{ getAmount($general->referral_commission) }}">
                                        <span class="input-group-text">
                                            %
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('How Many Times') <i class="la la-info-circle text--info" title="@lang('The number of times a referrer get commission from a single referee.')"></i>
                                    </label>
                                    <input class="form-control" name="commission_count" type="number" value="{{ $general->commission_count }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
            </div>
        </div>

    </form>
@endsection

@push('script-lib')
    <script src="{{ asset('assets/admin/js/spectrum.js') }}"></script>
@endpush

@push('style-lib')
    <link href="{{ asset('assets/admin/css/spectrum.css') }}" rel="stylesheet">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.colorPicker').spectrum({
                color: $(this).data('color'),
                change: function(color) {
                    $(this).parent().siblings('.colorCode').val(color.toHexString().replace(/^#?/, ''));
                }
            });

            $('.colorCode').on('input', function() {
                var clr = $(this).val();
                $(this).parents('.input-group').find('.colorPicker').spectrum({
                    color: clr,
                });
            });

            $('select[name=timezone]').val("'{{ config('app.timezone') }}'").select2();

            $('.select2-basic').select2({
                dropdownParent: $('.select2-parent')
            });
        })(jQuery);
    </script>
@endpush
@push('style')
    <style>
        .select2-parent {
            position: relative;
        }

        .tooltip {
            z-index: 99999999999;
        }
    </style>
@endpush
