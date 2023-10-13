@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title text-center">{{ __($pageTitle) }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.deposit.manual.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <h4 class="text-center mt-2">@lang('You have requested to pay') <b class="text--success">{{ showAmount($data['amount']) }} {{ __($general->cur_text) }}</b>

                                        </h4>

                                        <h5>
                                            @lang('Please pay') <b class="text--success">{{ showAmount($data['final_amo']) . ' ' . $data['method_currency'] }} </b> @lang('by following the information below')
                                        </h5>

                                        <p class="my-4 text-center">@php echo  $data->gateway->description @endphp</p>

                                    </div>

                                    <x-viser-form identifier="id" identifierValue="{{ $gateway->form_id }}" />

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn--base w-100  btn--xl">@lang('Pay Now')</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
