<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\ProcessSendMoney;
use App\Models\Country;
use App\Models\CountryDeliveryMethod;
use App\Models\Service;
use App\Models\GatewayCurrency;
use App\Models\SendingPurpose;
use App\Models\SendMoney;
use App\Models\Form;
use App\Models\Recipient;
use App\Models\SourceOfFund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SendMoneyController extends Controller
{

      /*
     * Return Send Money View Page
     */
    public function sendMoney()
    {
        $pageTitle = 'Send Money';

        $sources            = SourceOfFund::active()->get();
        $purposes           = SendingPurpose::active()->get();
        $sessionData        = session()->get('send_money') ?? [];
        $recipientCountryId = null;
        $deliveryMethodId   = null;
        $sendingCountries   = Country::active()->sending()->with('conversionRates')->get();
        $receivingCountries = Country::receivableCountries()->get();

        if ($sessionData) {
            $sendingCountryId   = $sendingCountries->where('id', $sessionData['sending_country'])->first()->id;
            $recipientCountryId = $receivingCountries->where('id', $sessionData['recipient_country'])->first()->id;
            $deliveryMethodId   = $sessionData['delivery_method'];
        } else {
            $ipInfo           = json_decode(json_encode(getIpInfo()), true);
            $countryName      = @implode(',', $ipInfo['country']);
            $sendingCountryId = @$sendingCountries->where('name', $countryName)->first()->id ?? @$sendingCountries->first()->id;
        }

        $todaySendMoney     = SendMoney::whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED])->where('user_id', auth()->id())->whereDate('created_at', now())->sum('base_currency_amount');
        $thisMonthSendMoney = SendMoney::whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED])->where('user_id', auth()->id())->whereMonth('created_at', now()->month)->sum('base_currency_amount');
        $recipients = Recipient::where('user_id' , auth()->user()->id)->get();
        
        $sendingAmount      = @$sessionData['sending_amount'];
        $recipientAmount    = @$sessionData['recipient_amount'];

        session()->forget('send_money');
        return view($this->activeTemplate . 'user.send_money.form', compact('pageTitle','recipients', 'sources', 'purposes', 'sendingAmount', 'recipientAmount', 'sendingCountryId', 'recipientCountryId', 'sendingCountries', 'receivingCountries', 'deliveryMethodId', 'todaySendMoney', 'thisMonthSendMoney'));
    }

      /*
     * Initially save the send-money data
     */
    public function save(Request $request)
    {


        $rules =  [
            'sending_amount'    => 'required|numeric|gt:0',
            'sending_country'   => 'required|gt:0',
            'recipient_country' => 'required|gt:0',
            'payment_type'      => 'required|in:1,2',
            'source_of_funds'   => 'required|gt:0',
            'sending_purpose'   => 'required|gt:0',
            'recipient'         => 'required|array|min:3',
            'recipient.*'       => 'required|string',
            'delivery_method'   => 'required|numeric',
            'service'           => 'nullable|required_unless:delivery_method,0|integer'
        ];

        $messages = [
            'recipient.name.required'    => 'Recipient name field is required',
            'recipient.mobile.required'  => 'Recipient mobile number field is required',
            'recipient.address.required' => 'Recipient address field is required',
            'service.required_unless'    => 'Service field is required if delivery method is not an agent'
        ];

        $serviceFormData = null;

        if ($request->service) {
            $service        = Service::findOrFail($request->service);
            $form           = Form::where('act', 'service_form')->findOrFail($service->form_id);
            $formData       = $form->form_data;
            $formProcessor  = new FormProcessor();
            $validationRule = $formProcessor->valueValidation($formData);
            $rules          = array_merge($rules, $validationRule);
            $request->validate($rules, $messages);
            $serviceFormData = $formProcessor->processFormData($request, $formData);
        } else {
            $request->validate($rules, $messages);
        }

        if ($request->delivery_method) {
            $countryDeliveryMethod = CountryDeliveryMethod::where('country_id', $request->recipient_country)->where('delivery_method_id', $request->delivery_method)->exists();
        } else {
            if (gs()->agent_module) {
                $receivingCountry = Country::findOrFail($request->recipient_country);
                if ($receivingCountry->has_agent) {
                    $countryDeliveryMethod = true;
                } else {
                    $countryDeliveryMethod = false;
                }
            } else {
                $countryDeliveryMethod = false;
            }
        }

        if (!$countryDeliveryMethod) {
            throw ValidationException::withMessages(['error' => 'Invalid delivery method selected']);
        }

        $user    = auth()->user();
        $payment = new ProcessSendMoney();

        if ($request->payment_type == 1 && $payment->amountWithCharge > $user->balance) {
            $notify[] = ['error', 'Insufficient balance'];
            return back()->withNotify($notify);
        }



        $payment->user       = $user;
        $payment->columnName = 'user_id';
        $sendMoney           = $payment->createSendMoney($request, $serviceFormData);

        if ($request->payment_type == 1) {
            $notify[] = ['success', 'Send money request submitted successfully'];
            $payment->createTransaction();
            ProcessSendMoney::updateSendMoney($sendMoney, $user);
            return to_route('user.send.money.history')->withNotify($notify);
        }

        session()->put('payment_trx', $sendMoney->trx);
        return to_route('user.send.money.pay.now');
    }

      /*
     * Redirect to payment page
     */
    public function payNow()
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('method_code')->get();

        $pageTitle = 'Pay Money';
        $trx       = session()->get('payment_trx');
        $sendMoney = SendMoney::filterUser()->with('sendingCountry:id,rate')->where('trx', $trx)->first();
        if (!$sendMoney) {
            $notify[] = ['error', 'The session is invalid'];
            return to_route('user.home')->withNotify($notify);
        }
        return view($this->activeTemplate . 'user.payment.payment', compact('gatewayCurrency', 'pageTitle', 'sendMoney'));
    }

      /*
     * Redirect to payment page to pay for previously initialized send-money
     */
    public function pay(Request $request)
    {
        $sendMoney = SendMoney::filterUser()->initiated()->findOrFail(decrypt($request->id));
        session()->put('payment_trx', $sendMoney->trx);
        return to_route('user.send.money.pay.now');
    }

      /*
     * Transfer History
     */
    public function history()
    {
        $pageTitle    = 'Send Money History';
        $emptyMessage = 'No send money found';
        $transfers    = SendMoney::with('deposit.gateway', 'recipientCountry', 'countryDeliveryMethod.deliveryMethod')->filterUser()->latest()->paginate(getPaginate());

        return view($this->activeTemplate . 'user.send_money.history', compact('pageTitle', 'emptyMessage', 'transfers'));
    }



    public function checkRecipient(Request $request)
    {
        $exist['data']  = false;
        $exist['type']  = null;
        $recipientQuery = Recipient::where('user_id', auth()->user()->id);
        if ($request->email) {
            $exist['data'] = $recipientQuery->where('email', @$request->email)->first();
            $exist['type'] = 'email';
        } elseif ($request->mobile) {
            $exist['data'] = $recipientQuery->where('mobile', @$request->mobile)->first();
            $exist['type'] = 'mobile';
        }

        return response($exist);
    }
}
