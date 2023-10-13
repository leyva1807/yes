@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $login = getContent('login.content', true);
    @endphp
    <div class="section login-section" style="background-image: url({{ getImage($activeTemplateTrue . 'images/auth-bg.jpg') }})">
        <div class="container">
            <div class="row g-4 g-lg-0 justify-content-between align-items-center">
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="{{ getImage('assets/images/frontend/login/' . @$login->data_values->image, '660x625') }}" alt="{{ $general->site_name }}" class="img-fluid">
                </div>
                <div class="col-lg-5">
                    @include($activeTemplate . 'user.auth.login_form')
                </div>
            </div>
        </div>
    </div>
@endsection
