<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Country;
use App\Models\CountryDeliveryMethod;
use App\Models\Recipient;
use App\Models\SendingPurpose;
use App\Models\SendMoney;
use App\Models\SourceOfFund;
use App\Models\Transaction;
use Illuminate\Validation\ValidationException;

class ProcessSendMoney
{
    public $user;
    public $columnName;
    public $sendingAmount;
    public $sendingCountry;
    public $recipientCountry;
    public $sourceOfFunds;
    public $sendingPurpose;

    public $sendingAmountInBaseCurrency;
    public $chargeInBaseCurrency;

    public $chargeInSendingCurrency;
    public $receivableAmount;

    public $amountWithCharge;
    public $conversionRate;

    public $countryDeliveryMethod;

    public $trx;
    public $sendMoneyId;

    public function __construct()
    {
        $request                     = request();
        $this->sendingAmount         = $request->sending_amount;
        $this->sendingCountry        = Country::with('conversionRates')->findOrFail($request->sending_country);
        $this->recipientCountry      = Country::findOrFail($request->recipient_country);
        $this->sourceOfFunds         = SourceOfFund::findOrFail($request->source_of_funds);
        $this->sendingPurpose        = SendingPurpose::findOrFail($request->sending_purpose);
        $this->countryDeliveryMethod = CountryDeliveryMethod::with('charge')->where('country_id', $request->recipient_country)->where('delivery_method_id', $request->delivery_method)->first();
        $this->trx                   = getTrx();
        $this->setAmountVariables();
    }

    public function createSendMoney($request, $serviceFormData = null)
    {


        if ($this->columnName == 'user_id') {
            $this->userTransferAbility();
        } else {
            $this->agentTransferAbility();
        }

        if ($request->save_recipient == 'on') {
            $recipient = Recipient::where('user_id', auth()->id())->where('mobile', @$request->recipient['mobile'])->where('email', @$request->recipient['email'])->first();

            if (!$recipient) {
                $recipientInfo          = new Recipient();
                $recipientInfo->user_id = $this->user->id;
                $recipientInfo->name    = @$request->recipient['name'];
                $recipientInfo->mobile  = @$request->recipient['mobile'];
                $recipientInfo->email   = @$request->recipient['email'];
                $recipientInfo->address = @$request->recipient['address'];
                $recipientInfo->save();
            }
        }

        $sendMoney              = new SendMoney();
        $column                 = $this->columnName;
        $sender                 = $request->sender;
        $recipient              = $request->recipient;
        $sendMoney->mtcn_number = getTrx(10);
        $sendMoney->$column     = $this->user->id;
        $sender['mobile']       = $this->sendingCountry->dial_code . @$request['sender']['mobile'];
        $sender['email']        = @$request['sender']['email'];
        $sender['address']      = @$request['sender']['address'];

        $recipient['mobile']  = $this->recipientCountry->dial_code . @$request['recipient']['mobile'];
        $recipient['email']   = @$request['recipient']['email'];
        $recipient['address'] = @$request['recipient']['address'];

        $sendMoney->service_id                 = $request->service ?? 0;
        $sendMoney->service_form_data          = $serviceFormData;
        $sendMoney->country_delivery_method_id = $this->countryDeliveryMethod->id ?? 0;
        $sendMoney->base_currency_amount       = $this->sendingAmountInBaseCurrency;
        $sendMoney->base_currency_charge       = $this->chargeInBaseCurrency;

        $sendMoney->sending_country_id   = $this->sendingCountry->id;
        $sendMoney->sending_currency     = $this->sendingCountry->currency;
        $sendMoney->sending_amount       = $this->sendingAmount;
        $sendMoney->sending_charge       = $this->chargeInSendingCurrency;
        $sendMoney->recipient_country_id = $this->recipientCountry->id;
        $sendMoney->recipient_currency   = $this->recipientCountry->currency;
        $sendMoney->recipient_amount     = $this->receivableAmount;
        $sendMoney->source_of_fund_id    = $this->sourceOfFunds->id;
        $sendMoney->sending_purpose_id   = $this->sendingPurpose->id;
        $sendMoney->trx                  = $this->trx;
        $sendMoney->conversion_rate      = $this->conversionRate;
        $sendMoney->base_currency_rate   = $this->sendingCountry->rate;
        $sendMoney->sender               = $sender;
        $sendMoney->recipient            = $recipient;
        $sendMoney->save();

        $this->sendMoneyId = $sendMoney->id;
        return $sendMoney;
    }

    private function userTransferAbility()
    {
        $general = gs();


        $sendingAmountInBaseCurrency = getAmount($this->sendingAmountInBaseCurrency);


        if ($this->user->per_send_money_limit != 0) {
            if ($sendingAmountInBaseCurrency > $this->user->per_send_money_limit) {
                throw ValidationException::withMessages(['error' => 'Sending amount exceeds the limit per transfer']);
            }
        } else {

            if ($sendingAmountInBaseCurrency > $general->user_send_money_limit) {
                throw ValidationException::withMessages(['error' => 'Sending amount exceeds the limit per transfer']);
            }
        }

        $allSendMoney = SendMoney::where('user_id', $this->user->id)->whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED]);


        //check monthly limit of user
        $thisMonthSendMoney = (clone $allSendMoney)->whereMonth('created_at', now()->month)->sum('base_currency_amount');


        if ($this->user->monthly_send_money_limit != 0) {

            $thisMonthAvailable = getAmount($this->user->monthly_send_money_limit - $thisMonthSendMoney);
        } else {

            $thisMonthAvailable = getAmount($general->user_monthly_send_money_limit - $thisMonthSendMoney);
        }

        if ($thisMonthAvailable < $sendingAmountInBaseCurrency) {
            throw ValidationException::withMessages(['error' => 'Your monthly send money amount exceeds the monthly send money limit']);
        }


        //check daily limit of user
        $todaySendMoney = (clone $allSendMoney)->whereDate('created_at', now())->sum('base_currency_amount');

        if ($this->user->daily_send_money_limit != 0) {

            $todayAvailable = getAmount($this->user->daily_send_money_limit - $todaySendMoney);
        } else {
            $todayAvailable = getAmount($general->user_daily_send_money_limit - $todaySendMoney);
        }


        if ($todayAvailable < $sendingAmountInBaseCurrency) {
            throw ValidationException::withMessages(['error' => 'Your daily send money amount exceeds the daily send money limit']);
        }
    }

    private function agentTransferAbility()
    {
        $general                     = gs();
        $sendingAmountInBaseCurrency = getAmount($this->sendingAmountInBaseCurrency);
        if ($sendingAmountInBaseCurrency > $general->agent_send_money_limit) {
            throw ValidationException::withMessages(['error' => 'Sending amount exceeds the limit per transfer']);
        }
        $allSendMoney = SendMoney::where('agent_id', $this->user->id)->whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED]);

        //check monthly limit of agent
        $thisMonthSendMoney = (clone $allSendMoney)->whereMonth('created_at', now()->month)->sum('base_currency_amount');
        $thisMonthAvailable = getAmount($general->agent_monthly_send_money_limit - $thisMonthSendMoney);

        //check daily limit of agent
        $todaySendMoney = (clone $allSendMoney)->whereDate('created_at', now())->sum('base_currency_amount');
        $todayAvailable = getAmount($general->agent_daily_send_money_limit - $todaySendMoney);

        if ($todayAvailable < $sendingAmountInBaseCurrency) {
            throw ValidationException::withMessages(['error' => 'Your daily send money amount exceeds the daily send money limit']);
        }

        if ($thisMonthAvailable < $sendingAmountInBaseCurrency) {
            throw ValidationException::withMessages(['error' => 'Your monthly send money amount exceeds the monthly send money limit']);
        }
    }

    public function setAmountVariables()
    {
        $senderToReceiverConversionRate = $this->sendingCountry->conversionRates->where('to_country', $this->recipientCountry->id)->first();


        if ($senderToReceiverConversionRate) {
            $conversionRate = $senderToReceiverConversionRate->rate;
        } else {
            $conversionRate = $this->recipientCountry->rate / $this->sendingCountry->rate;
        }

        $this->conversionRate = $conversionRate;
        $receivableAmount     = $conversionRate * $this->sendingAmount;  //In Recipient's currency

        if ($this->countryDeliveryMethod) {
            $fixedCharge   = @$this->countryDeliveryMethod->charge->fixed_charge ?? 0;
            $percentCharge = @$this->countryDeliveryMethod->charge->percent_charge ?? 0;
        } else {
            $general       = gs();
            $fixedCharge   = @$general->agent_charges->fixed_charge ?? 0;
            $percentCharge = @$general->agent_charges->percent_charge ?? 0;
        }

        $percentCharge             = $receivableAmount * $percentCharge / 100;
        $chargeInRecipientCurrency = $fixedCharge + $percentCharge;
        $chargeInSendingCurrency   = $chargeInRecipientCurrency / $conversionRate;

        $this->chargeInBaseCurrency        = $chargeInSendingCurrency / $this->sendingCountry->rate;
        $this->sendingAmountInBaseCurrency = $this->sendingAmount / $this->sendingCountry->rate;
        $this->chargeInSendingCurrency     = $chargeInSendingCurrency;
        $this->receivableAmount            = $receivableAmount;
        $this->amountWithCharge            = $this->sendingAmountInBaseCurrency + $this->chargeInBaseCurrency;
    }


    public static function updateSendMoney($sendMoney, $user)
    {
        $sendMoney->payment_status = Status::PAYMENT_SUCCESS;
        $sendMoney->status         = Status::SEND_MONEY_PENDING;
        $sendMoney->save();
        $general = gs();

        if ($general->referral_system && $sendMoney->user_id && $user->ref_by && $general->commission_count > $user->total_bonus_given) {
            giveReferralCommission($user, $sendMoney);
        }

        notify($user, 'PAYMENT_COMPLETE', [
            'mtcn_number'        => $sendMoney->mtcn_number,
            'trx'                => $sendMoney->trx,
            'sending_country'    => @$sendMoney->sendingCountry->name,
            'sending_amount'     => showAmount($sendMoney->sending_amount),
            'sending_currency'   => $sendMoney->sending_currency,
            'recipient__country' => @$sendMoney->recipientCountry->name,
            'recipient_amount'   => showAmount($sendMoney->recipient_amount),
            'recipient_currency' => $sendMoney->recipient_currency,
        ]);
    }

    public function createTransaction()
    {
        $this->user->balance -= $this->amountWithCharge;
        $this->user->save();

        $adminNotification          = new AdminNotification();
        $user                       = $this->user;
        $column                     = $this->columnName;
        $adminNotification->$column = $user->id;

        $adminNotification->title     = 'Send money to ' . $this->recipientCountry->name;
        $adminNotification->click_url = urlPath('admin.send.money.details', $this->sendMoneyId);
        $adminNotification->save();

        $transaction               = new Transaction();
        $transaction->$column      = $this->user->id;
        $transaction->amount       = $this->amountWithCharge;
        $transaction->post_balance = $this->user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->details      = 'Send money to ' . $this->recipientCountry->name;
        $transaction->remark       = 'send_money_payment';
        $transaction->trx          = $this->trx;
        $transaction->save();
    }
}
