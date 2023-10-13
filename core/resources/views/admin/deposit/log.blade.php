@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        @if (request()->routeIs("admin.$type.list") || request()->routeIs("admin.$type.method"))
            <div class="col-xxl-3 col-sm-6 mb-30">
                <x-widget bg="success" color="white" link='{{ route("admin.$type.successful") }}' style="4" title="Successful {{ ucfirst($type) }}" value="{{ __($general->cur_sym) }}{{ showAmount($successful) }}" />
            </div>
            <div class="col-xxl-3 col-sm-6 mb-30">
                <x-widget bg="6" color="white" link='{{ route("admin.$type.pending") }}' style="4" title="Pending {{ ucfirst($type) }}" value="{{ __($general->cur_sym) }}{{ showAmount($pending) }}" />
            </div>
            <div class="col-xxl-3 col-sm-6 mb-30">
                <x-widget bg="pink" color="white" link='{{ route("admin.$type.rejected") }}' style="4" title="Rejected {{ ucfirst($type) }}" value="{{ __($general->cur_sym) }}{{ showAmount($rejected) }}" />
            </div>
            <div class="col-xxl-3 col-sm-6 mb-30">
                <x-widget bg="dark" color="white" link='{{ route("admin.$type.initiated") }}' style="4" title="Initiated {{ ucfirst($type) }}" value="{{ __($general->cur_sym) }}{{ showAmount($initiated) }}" />
            </div>
        @endif

        <div class="col-md-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway | Transaction')</th>
                                    <th>@lang('Initiated')</th>
                                    @if ($type == 'payment')
                                        <th>@lang('MTCN Number')</th>
                                        <th>@lang('User')</th>
                                    @else
                                        <th>@lang('Agent')</th>
                                    @endif

                                    <th>@lang('Amount')</th>
                                    <th>@lang('Conversion')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                    @php
                                        $details = $deposit->detail ? json_encode($deposit->detail) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-bold"> <a href="{{ appendQuery('method', @$deposit->gateway->alias) }}">{{ __(@$deposit->gateway->name) }}</a> </span>
                                            <br>
                                            <small> {{ $deposit->trx }} </small>
                                        </td>

                                        <td>
                                            {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                                        </td>

                                        @if ($type == 'payment')
                                            <td>
                                                @if (@$deposit->sendMoney->status == Status::SEND_MONEY_PENDING || @$deposit->sendMoney->status == Status::SEND_MONEY_COMPLETED && $deposit->status != Status::PAYMENT_REJECT)
                                                    <a href="{{ route('admin.send.money.all') }}?search={{ @$deposit->sendMoney->mtcn_number }}">{{ @$deposit->sendMoney->mtcn_number }}</a>
                                                @else
                                                    <span class="fw-bold">@lang('Not Available')</span>
                                                @endif
                                            </td>

                                            <td>
                                                <span class="fw-bold">{{ @$deposit->user->fullname }}</span>
                                                <br>
                                                <span class="small">
                                                    <a href="{{ appendQuery('search', @$deposit->user->username) }}"><span>@</span>{{ @$deposit->user->username }}</a>
                                                </span>
                                            </td>
                                        @else
                                            <td>
                                                <span class="fw-bold">{{ @$deposit->agent->fullname }}</span>
                                                <br>
                                                <span class="small">
                                                    <a href="{{ appendQuery('search', @$deposit->agent->username) }}"><span>@</span>{{ @$deposit->agent->username }}</a>
                                                </span>
                                            </td>
                                        @endif

                                        <td>
                                            {{ __($general->cur_sym) }}{{ showAmount($deposit->amount) }} +
                                            <span class="text-danger" title="@lang('charge')">{{ __($general->cur_sym) }}{{ showAmount($deposit->charge) }} </span>
                                            <br>
                                            <strong title="@lang('Amount with charge')">
                                                {{ showAmount($deposit->amount + $deposit->charge) }} {{ __($general->cur_text) }}
                                            </strong>
                                        </td>
                                        <td>
                                            1 {{ __($general->cur_text) }} = {{ showAmount($deposit->rate) }} {{ __($deposit->method_currency) }}
                                            <br>
                                            <strong>{{ showAmount($deposit->final_amo) }} {{ __($deposit->method_currency) }}</strong>
                                        </td>
                                        <td>
                                            @php echo $deposit->statusBadge @endphp
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-outline--primary ms-1" href='{{ route("admin.$type.details", $deposit->id) }}'>
                                                <i class="la la-desktop"></i>@lang('Details')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($deposits->hasPages())
                    <div class="card-footer py-4">
                        @php echo paginateLinks($deposits) @endphp
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form dateSearch='yes' />
@endpush
