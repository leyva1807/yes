@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title">
                                {{ __($pageTitle) }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <form class="register" action="" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('First Name')</label>
                                        <input type="text" class="form-control form--control" name="firstname"
                                            value="{{ $user->firstname }}" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Last Name')</label>
                                        <input type="text" class="form-control form--control" name="lastname"
                                            value="{{ $user->lastname }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('E-mail Address')</label>
                                        <input class="form-control form--control" value="{{ $user->email }}" readonly>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Mobile Number')</label>
                                        <input class="form-control form--control" value="{{ $user->mobile }}" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('Address')</label>
                                        <input type="text" class="form-control form--control" name="address"
                                            value="{{ @$user->address->address }}">
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label class="d-block sm-text mb-2">@lang('State')</label>
                                        <input type="text" class="form-control form--control" name="state"
                                            value="{{ @$user->address->state }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-sm-3">
                                        <label class="d-block sm-text mb-2">@lang('Zip Code')</label>
                                        <input type="text" class="form-control form--control" name="zip"
                                            value="{{ @$user->address->zip }}">
                                    </div>

                                    <div class="form-group col-sm-3">
                                        <label class="d-block sm-text mb-2">@lang('City')</label>
                                        <input type="text" class="form-control form--control" name="city"
                                            value="{{ @$user->address->city }}">
                                    </div>

                                    <div class="form-group col-sm-3">
                                        <label class="d-block sm-text mb-2">@lang('Country')</label>
                                        <input class="form-control form--control" value="{{ @$user->address->country }}"
                                            disabled>
                                    </div>
                                    <div class="form-group col-sm-3">
                                        <label class="d-block sm-text mb-2">@lang('Acount Type')</label>
                                        @if ($user->type==0)
                                            <input class="form-control form--control" value="Personal" disabled>
                                        @else
                                            <input class="form-control form--control" value="Business" disabled>
                                        @endif
                                    </div>

                                </div>

                                @if ($user->type == 1)
                                    <div class="row justify-content-center">
                                        <div class="col-lg-12">

                                            @if ($user->business_profile_data)
                                                <ul class="list-group ">
                                                    @foreach ($user->business_profile_data as $val)
                                                        @continue(!$val->value)
                                                        <li
                                                            class="list-group-item list-group-flush d-flex justify-content-between align-items-center">
                                                            {{ __($val->name) }}
                                                            <span>
                                                                @if ($val->type == 'checkbox')
                                                                    {{ implode(',', $val->value) }}
                                                                @elseif($val->type == 'file')
                                                                    @if ($val->value)
                                                                        <a href="{{ route('admin.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}"
                                                                            class="me-3"><i class="fa fa-file"></i>
                                                                            @lang('Attachment') </a>
                                                                    @else
                                                                        @lang('No File')
                                                                    @endif
                                                                @else
                                                                    <p>{{ __($val->value) }}</p>
                                                                @endif
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                        </div>
                                    </div>
                                @endif


                                <div class="form-group">
                                    <button type="submit"
                                        class="btn btn--base w-100 mt-3 btn--xl">@lang('Submit')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
