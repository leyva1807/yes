<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\SendMoney;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class SendMoneyController extends Controller
{

    public function index()
    {
        $segments       = request()->segments();
        $scope          = end($segments);
        $pageTitle      = ucfirst($scope) . " Send Money";

        if (request()->search) {
            $pageTitle .= ' - ' . request()->search;
        }

        $sendMoneys     = SendMoney::where('status', '!=', Status::SEND_MONEY_INITIATED);
        if ($scope != 'all') {
            $sendMoneys = $sendMoneys->$scope();
        }

        if (request()->recipient) {
         
            $sendMoneys->where('recipient->name', request()->recipient);
        }
        if (request()->sending_amount) {
          
            $sendMoneys->where('sending_amount','>=', request()->sending_amount);
        }

        $sendMoneys     = $sendMoneys->searchable(['mtcn_number', 'sender', 'recipient', 'agent:username'])->filter(['mtcn_number','user:username','agent:username'])->dateFilter()->orderBy('id', 'desc')->with('user', 'agent', 'countryDeliveryMethod.deliveryMethod', 'sendingCountry', 'recipientCountry')->paginate(getPaginate());

        return view('admin.send_money.list', compact('pageTitle', 'sendMoneys'));
    }

    public function details($id = null)
    {
        $sendMoney = SendMoney::with(['user', 'agent', 'payoutBy', 'service', 'countryDeliveryMethod.deliveryMethod', 'sendingCountry', 'recipientCountry'])->findOrFail($id);
        $pageTitle = 'Send money to ' . @$sendMoney->recipientCountry->name . ' from ' . @$sendMoney->sendingCountry->name;
        return view('admin.send_money.detail', compact('pageTitle', 'sendMoney'));
    }

    public function refundMoney(Request $request, $id = null)
    {
        $request->validate([
            'message' => 'required'
        ], [
            'message.required' => 'Please write a feedback'
        ]);

        $sendMoney                 = SendMoney::with('sendingCountry', 'recipientCountry')->where('status', Status::SEND_MONEY_PENDING)->findOrFail($id);
        $sendMoney->status         = Status::SEND_MONEY_REFUNDED;
        $sendMoney->admin_feedback = $request->message;
        $sendMoney->save();

        $transaction               = new Transaction();
        if ($sendMoney->user_id) {
            $user                  = $sendMoney->user;
            $transaction->user_id  = $sendMoney->user_id;
        } else {
            $user                  = $sendMoney->agent;
            $transaction->agent_id = $sendMoney->agent_id;
        }
        $user->balance += $sendMoney->base_currency_amount;
        $user->save();
        $transaction->amount       = $sendMoney->base_currency_amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->details      = 'Refunded sent money. Message: ' . $request->message;
        $transaction->trx          = $sendMoney->trx;
        $transaction->save();

        notify($user, 'SEND_MONEY_REFUND', [
            'trx'                => $sendMoney->trx,
            'sending_country'    => @$sendMoney->sendingCountry->name,
            'sending_amount'     => showAmount($sendMoney->sending_amount),
            'sending_currency'   => $sendMoney->sending_currency,
            'recipient_country' => @$sendMoney->recipientCountry->name,
            'recipient_amount'   => showAmount($sendMoney->recipient_amount),
            'recipient_currency' => $sendMoney->recipient_currency,
            'message'            => $request->message,
        ]);
        $notify[] = ['success', 'This send money is refunded successfully'];
        return back()->withNotify($notify);
    }

    public function payTheReceiver($id)
    {
        $sendMoney = SendMoney::with('user', 'recipientCountry')->findOrFail($id);
        $sendMoney->status = Status::SEND_MONEY_COMPLETED;
        $sendMoney->save();

        if (@$sendMoney->user) {
            $user = $sendMoney->user;
        } else {
            $sender = $sendMoney->sender;
            $user = new User();
            $user->username = @$sender->firstname . @$sender->lastname;
            $user->email = $sender->email;
            $user->mobile = @$sender->mobile;
        }

        notify($user, 'SEND_MONEY_RECEIVED', [
            'recipient_country'  => @$sendMoney->recipientCountry->name,
            'recipient_amount'   => showAmount($sendMoney->recipient_amount, 3),
            'recipient_currency' => $sendMoney->recipient_currency,
            'trx'                => $sendMoney->trx
        ]);

        $notify[] = ['success', 'Send money completed successfully'];
        return to_route('admin.send.money.all')->withNotify($notify);
    }
}
