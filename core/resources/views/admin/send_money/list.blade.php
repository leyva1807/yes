@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('MTCN')</th>
                                    <th>@lang('Created By')</th>
                                    <th>@lang('Sender')</th>
                                    <th>@lang('Recipient')</th>
                                    <th>@lang('Delivery Method')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sendMoneys as $sendMoney)
                                    <tr>
                                        <td>
                                            <span class="text--muted fw-bold">#{{ @$sendMoney->mtcn_number }}</span>
                                            <br>
                                            <em class="text--muted text--small">{{ showDateTime(@$sendMoney->created_at) }}</em>
                                        </td>

                                        <td>
                                            @if ($sendMoney->user_id)
                                                <span class="fw-bold">{{ @$sendMoney->user->fullname }}</span>
                                                <br>
                                                <span class="small">
                                                    <a href="{{ route('admin.users.detail', $sendMoney->user_id) }}"><span>@</span>{{ @$sendMoney->user->username }}</a>
                                                </span>
                                            @else
                                                <span class="fw-bold">{{ @$sendMoney->agent->fullname }}</span>
                                                <br>
                                                <span class="small">
                                                    <a href="{{ route('admin.agents.detail', $sendMoney->agent_id) }}"><span>@</span>{{ @$sendMoney->agent->username }}</a>
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ @$sendMoney->senderInfo->name }}
                                            <br>
                                            <a href="{{ route('admin.country.index') }}?search={{ @$sendMoney->sendingCountry->name }}" class="fw-bold">{{ __(@$sendMoney->sendingCountry->name) }}</a>
                                        </td>

                                        <td>
                                            {{ $sendMoney->recipient->name }}<br>
                                            <a href="{{ route('admin.country.index') }}?search={{ @$sendMoney->recipientCountry->name }}" class="fw-bold">{{ __($sendMoney->recipientCountry->name) }}</a>
                                        </td>

                                        <td>
                                            @if ($sendMoney->country_delivery_method_id)
                                                <span class="fw-bold text--danger">{{ __(@$sendMoney->countryDeliveryMethod->deliveryMethod->name) }}</span>
                                            @else
                                                <span class="text--info fw-bold">@lang('Agent')</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span>{{ showAmount($sendMoney->sending_amount) }} {{ @$sendMoney->sending_currency }}</span>
                                            <i class="la la-arrow-right"></i>
                                            <span>{{ showAmount($sendMoney->recipient_amount) }} {{ __($sendMoney->recipient_currency) }}</span>
                                        </td>

                                        <td>
                                            @php
                                                echo $sendMoney->statusBadge;
                                            @endphp
                                            <br>
                                            {{ diffForHumans($sendMoney->updated_at) }}
                                        </td>

                                        <td>
                                            <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.send.money.details', $sendMoney->id) }}">
                                                <i class="las la-desktop"></i>@lang('Details')
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
                @if ($sendMoneys->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($sendMoneys) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

{{-- filter --}}

  
  <div class="offcanvas offcanvas-end" tabindex="-1" id="filter" aria-labelledby="filterLabel">
    <div class="offcanvas-header">
        <h5 class="ms-3">@lang('Filter')</h5>
      <button type="button" class="btn-close text-reset me-2 " data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">

        <form action="">
            <div class="modal-body">

                <div class="form-group">
                    <label>@lang('User Username') </label>
                    <input class="form-control" name="user" type="text" value="{{ request()->user ?? ''}}" />
                </div>
                <div class="form-group">
                    <label>@lang('Agent Username') </label>
                    <input class="form-control" name="agent" type="text" value="{{ request()->agent ?? '' }}" />
                </div>

                <div class="form-group">
                    <label>@lang('Recipient Name') </label>
                    <input class="form-control" name="recipient" type="text" value="{{ request()->recipient ?? '' }}" />
                </div>

                <div class="form-group">
                    <label>@lang('MTCN Number') </label>
                    <input class="form-control" name="mtcn_number" type="text" value="{{ request()->mtcn_number ?? '' }}" />
                </div>
                <div class="form-group">
                    <label>@lang('Amount') </label>
                    <input class="form-control" name="sending_amount" type="text" value="{{ request()->sending_amount ?? '' }}" />
                </div>

                <div class="flex-grow-1">
                    <label>@lang('Date')</label>
                    <input name="date" type="text"  data-range="true" data-multiple-dates-separator=" - " data-language="en" class="datepicker-here form-control" data-position='bottom right' placeholder="@lang('Start date - End date')" autocomplete="off" value="{{ request()->date }}">
                </div>
             
            </div>
            <div class="modal-footer">
                <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
            </div>
        </form>
    
    </div>
  </div>


@endsection

@push('style')
<style>
    .datepickers-container {
    z-index: 9999;
}
</style>
@endpush

@push('breadcrumb-plugins')



    <x-search-form placeholder="MTCN/Sender/Recipient" />

    <button class="btn btn--primary h-45" data-bs-toggle="offcanvas" href="#filter" role="button" aria-controls="filter"> <i class="fas fa-filter"></i> @lang('Filter')</button>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/datepicker.en.js') }}"></script>
@endpush
@push('script')
    <script>
        (function($) {
            "use strict";
      

            if (!$('.datepicker-here').val()) {
                $('.datepicker-here').datepicker();
            }
        })(jQuery)
    </script>
@endpush
