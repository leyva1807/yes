@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $register = getContent('register.content', true);
        $policyPages = getContent('policy_pages.element', false, null, true);
    @endphp
    <div class="section login-section"
        style="background-image: url({{ getImage($activeTemplateTrue . 'images/auth-bg.jpg') }})">
        <div class="container">
            <div class="row g-4 g-xl-0 justify-content-between align-items-center">
                <div class="col-lg-4 col-xl-6 d-none d-lg-block">
                    <img alt="{{ $general->site_name }}" class="img-fluid"
                        src="{{ getImage('assets/images/frontend/register/' . @$register->data_values->image, '660x625') }}">
                </div>
                <div class="col-lg-8 col-xl-6">
                    <div class="login__right bg--light">

                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <a href="{{ route('user.register') }}" class="nav-link {{ request()->type != 'business'  ? 'active' : ''}}">@lang('Personal')</a>
                                <a href="{{ route('user.register')}}?type=business" class="nav-link {{ request()->type == 'business'  ? 'active' : ''}}">@lang('Business')</a>
                            </div>
                        </nav>
                        <div class="tab-content mt-4" id="nav-tabContent">
                            <form action="{{ route('user.register') }}" autocomplete="off"
                                class="login__form row g-3 g-sm-4 verify-gcaptcha" method="POST">
                                @csrf

                                <input type="hidden" value="1" name="type">
                                <div class="col-sm-6 col-xl-6 ">

                                    <div class="form-group">

                                        <label class="form-label sm-text t-heading-font heading-clr fw-md"
                                            for="company_name">@lang('Company Name')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="la la-home"></i>
                                            </span>
                                            <input class="form-control form--control " name="company_name" required
                                                type="text" value="{{ old('company_name') }}">
                                        </div>
                                    </div>


                                </div>

                                <div class="col-sm-6 col-xl-6 ">
                                    <div class="forn-group">
                                        <label class="form-label sm-text t-heading-font heading-clr fw-md"
                                            for="owner_name">@lang('Owner Name')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="la la-user-tie"></i>
                                            </span>
                                            <input class="form-control form--control " name="owner_name" required
                                                type="text" value="{{ old('owner_name') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-6 ">

                                    <div class="form-group">
                                        <label class="form-label sm-text t-heading-font heading-clr fw-md"
                                            for="business_username">@lang('Username')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="la la-user"></i>
                                            </span>
                                            <input class="form-control form--control checkBusinessUser" name="username"
                                                required type="text" value="{{ old('username') }}"
                                                id="business_username">
                                        </div>
                                        <small class="text-danger usernameExist"></small>
                                    </div>
                                </div>


                                <div class="col-sm-6 col-xl-6 ">

                                    <div class="form-group">

                                        <label class="form-label sm-text t-heading-font heading-clr fw-md"
                                            for="business_email">@lang('Email')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="las la-envelope"></i>
                                            </span>
                                            <input class="form-control checkBusinessUser form--control" name="email"
                                                required type="email" value="{{ old('email') }}" id="business_email">
                                        </div>
                                    </div>


                                </div>
                                <div class="col-sm-6 col-xl-6 ">

                                    <div class="form-group">
                                        <label class="form-label sm-text t-heading-font heading-clr fw-md">
                                            @lang('Country')
                                        </label>
                                        <div class="input-group input--group">
                                            <span class="input-group-text">
                                                <i class="las la-globe"></i>
                                            </span>
                                            <div class="form--select-light">
                                                <select aria-label="Default select example"
                                                    class="form-select form--select" name="country" id="country1">
                                                    @foreach ($countries as $key => $country)
                                                        <option data-code="{{ $key }}"
                                                            data-mobile_code="{{ $country->dial_code }}"
                                                            value="{{ $country->country }}">
                                                            {{ __($country->country) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                    </div>


                                </div>
                                <div class="col-sm-6 col-xl-6 ">
                                    <div class="form-group">

                                        <label class="form-label sm-text t-heading-font heading-clr fw-md"
                                            for="business_mobile">@lang('Mobile')</label>
                                        <div class="input-group">
                                            <span class="input-group-text mobile-code">
                                            </span>
                                            <input name="mobile_code" type="hidden">
                                            <input name="country_code" type="hidden">
                                            <input class="form-control form--control checkBusinessUser" name="mobile"
                                                type="number" value="{{ old('mobile') }}" id="business_mobile">
                                        </div>
                                        <small class="text-danger mobileExist"></small>
                                    </div>

                                </div>
                                <div class="col-sm-6 col-xl-6 ">

                                    <div class="form-group">

                                        <label class="form-label sm-text t-heading-font heading-clr fw-md"
                                            for="business_password">@lang('Password')</label>
                                        <div class="input-group hover-input-popup ">
                                            <span class="input-group-text">
                                                <i class="las la-lock"></i>
                                            </span>
                                            <input class="form-control form--control border-end-0" name="password"
                                                type="password" id="business_password" />
                                            <span class="input-group-text pass-toggle border-start-0">
                                                <i class="las la-eye-slash"></i>
                                            </span>
                                            @if ($general->secure_password)
                                                <div class="input-popup">
                                                    <p class="error lower">@lang('1 small letter minimum')</p>
                                                    <p class="error capital">@lang('1 capital letter minimum')</p>
                                                    <p class="error number">@lang('1 number minimum')</p>
                                                    <p class="error special">@lang('1 special character minimum')</p>
                                                    <p class="error minimum">@lang('6 character password')</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>


                                </div>

                                <div class="col-sm-6 col-xl-6 ">

                                    <div class="form-group">


                                        <label class="form-label sm-text t-heading-font heading-clr fw-md"
                                            for="business_confirm_password">@lang('Confirm Password')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="las la-lock"></i>
                                            </span>
                                            <input autocomplete="new-password"
                                                class="form-control form--control border-end-0""
                                                name="password_confirmation" required type="password"
                                                id="business_confirm_password" />
                                            <span class="input-group-text pass-toggle border-start-0">
                                                <i class="las la-eye-slash"></i>
                                            </span>
                                        </div>
                                    </div>



                                </div>

                                <div class="col-12">
                                    <x-captcha />
                                </div>

                                @if ($general->agree)
                                    <div class="form-group">
                                        <input type="checkbox" @checked(old('agree')) name="agree" id="agree1"
                                            required>
                                        <label for="agree1">@lang('I agree with')</label> <span>
                                            @foreach ($policyPages as $policy)
                                                <a href="{{ route('policy.pages', [slug($policy->data_values->title), $policy->id]) }}"
                                                    target="_blank">{{ __($policy->data_values->title) }}</a>
                                                @if (!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach
                                        </span>
                                    </div>
                                @endif
                                <div class="col-12">
                                    <button class="btn btn--xl btn--base w-100 btn--xl"> @lang('Submit') </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {{-- end --}}

    <div aria-hidden="true" aria-labelledby="existModalCenterTitle" class="modal fade" id="existModalCenter"
        role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pt-0">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <span aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <h6 class="text-center">@lang('You already have an account please Login ')</h6>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-dark btn-sm" data-bs-dismiss="modal"
                        type="button">@lang('Close')</button>
                    <a class="btn btn--base btn-sm" href="{{ route('user.login') }}">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <style>
        .nav-link {
            padding: 0.5rem 7.13rem;
        }
    </style>
@endpush

@if ($general->secure_password)
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

@push('script')
    <script>
        "use strict";
        (function($) {
            @if ($mobileCode)
                $(`option[data-code={{ $mobileCode }}]`).attr('selected', '');
            @endif

            $('select[name=country]').change(function() {
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
            });
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));




            $('.checkUser,.checkBusinessUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';
                if ($(this).attr('name') == 'mobile') {
                    var mobile = `${$('.mobile-code').text().substr(1)}${value}`;
                    var data = {
                        mobile: mobile,
                        _token: token
                    }
                }
                if ($(this).attr('name') == 'email') {
                    var data = {
                        email: value,
                        _token: token
                    }
                }
                if ($(this).attr('name') == 'username') {
                    var data = {
                        username: value,
                        _token: token
                    }
                }
                $.post(url, data, function(response) {
                    if (response.data != false && response.type == 'email') {
                        $('#existModalCenter').modal('show');
                    } else if (response.data != false) {
                        $(`.${response.type}Exist`).text(`${response.type} already exist`);
                    } else {
                        $(`.${response.type}Exist`).text('');
                    }
                });
            });


        })(jQuery);
    </script>
@endpush
