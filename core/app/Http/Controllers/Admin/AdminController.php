<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\AdminNotification;
use App\Models\Agent;
use App\Models\Country;
use App\Models\Deposit;
use App\Models\SendMoney;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller {

    public function dashboard() {
        $pageTitle = 'Dashboard';
        // User Info
        $widget['total_users']             = User::count();
        $widget['verified_users']          = User::active()->count();
        $widget['email_unverified_users']  = User::emailUnverified()->count();
        $widget['mobile_unverified_users'] = User::mobileUnverified()->count();

        $widget['total_agent']             = Agent::count();
        $widget['active_agent']            = Agent::active()->count();
        $widget['kycUnverified']           = Agent::kycUnverified()->count();
        $widget['kycPending']              = Agent::kycPending()->count();


        // user Browsing, Country, Operating Log
        $userLoginData                 = UserLogin::where('created_at', '>=', now()->subDay(30))->get(['browser', 'os', 'country']);

        $chart['user_browser_counter'] = $userLoginData->groupBy('browser')->map(function ($item, $key) {
            return collect($item)->count();
        });

        $chart['user_os_counter']      = $userLoginData->groupBy('os')->map(function ($item, $key) {
            return collect($item)->count();
        });

        $chart['user_country_counter'] = $userLoginData->groupBy('country')->map(function ($item, $key) {
            return collect($item)->count();
        })->sort()->reverse()->take(5);

        // SendMoney Info
        $sendMoney['total']                     = SendMoney::where('payment_status', Status::PAYMENT_SUCCESS)->count();
        $sendMoney['pending']                   = SendMoney::pending()->count();
        $sendMoney['completed']                 = SendMoney::completed()->count();
        $sendMoney['refunded']                  = SendMoney::refunded()->count();

        $deposit['total_deposit_amount']        = Deposit::successful()->where('agent_id', '!=', 0)->sum('amount');
        $deposit['total_deposit_pending']       = Deposit::pending()->where('agent_id', '!=', 0)->count();
        $deposit['total_deposit_rejected']      = Deposit::rejected()->where('agent_id', '!=', 0)->count();
        $deposit['total_deposit_charge']        = Deposit::successful()->where('agent_id', '!=', 0)->sum('charge');

        $payment['total_payment_amount']        = Deposit::successful()->where('user_id', '!=', 0)->sum('amount');
        $payment['total_payment_pending']       = Deposit::pending()->where('user_id', '!=', 0)->count();
        $payment['total_payment_rejected']      = Deposit::rejected()->where('user_id', '!=', 0)->count();
        $payment['total_payment_charge']        = Deposit::successful()->where('user_id', '!=', 0)->sum('charge');

        $withdrawals['total_withdraw_amount']   = Withdrawal::approved()->sum('amount');
        $withdrawals['total_withdraw_pending']  = Withdrawal::pending()->count();
        $withdrawals['total_withdraw_rejected'] = Withdrawal::rejected()->count();
        $withdrawals['total_withdraw_charge']   = Withdrawal::approved()->sum('charge');

        // Transaction Graph
        $trxReport['date']  = collect([]);
        $plusTrx            = Transaction::where('trx_type', '+')->where('created_at', '>=', now()->subDays(30))
            ->selectRaw("SUM(amount) as amount, DATE_FORMAT(created_at,'%Y-%m-%d') as date")
            ->orderBy('created_at')
            ->groupBy('date')
            ->get();

        $plusTrx->map(function ($trxData) use ($trxReport) {
            $trxReport['date']->push($trxData->date);
        });

        $minusTrx = Transaction::where('trx_type', '-')->where('created_at', '>=', now()->subDays(30))
            ->selectRaw("SUM(amount) as amount, DATE_FORMAT(created_at,'%Y-%m-%d') as date")
            ->orderBy('created_at')
            ->groupBy('date')
            ->get();

        $minusTrx->map(function ($trxData) use ($trxReport) {
            $trxReport['date']->push($trxData->date);
        });

        $trxReport['date'] = dateSorting($trxReport['date']->unique()->toArray());


        // Monthly Deposit & Withdraw Report Graph
        $report['months']                = collect([]);
        $report['deposit_month_amount']  = collect([]);
        $report['withdraw_month_amount'] = collect([]);

        $depositsMonth = Deposit::where('created_at', '>=', now()->subYear())
            ->where('status', Status::PAYMENT_SUCCESS)
            ->where('agent_id', '!=', 0)
            ->selectRaw("SUM( CASE WHEN (status = " . Status::PAYMENT_SUCCESS . " AND agent_id != 0) THEN amount END) as depositAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $depositsMonth->map(function ($depositData) use ($report) {
            $report['months']->push($depositData->months);
            $report['deposit_month_amount']->push(getAmount($depositData->depositAmount));
        });

        $withdrawalMonth = Withdrawal::where('created_at', '>=', now()->subYear())
            ->where('agent_id', '!=', 0)
            ->where('status', Status::PAYMENT_SUCCESS)
            ->selectRaw("SUM( CASE WHEN (status = " . Status::PAYMENT_SUCCESS . " AND agent_id != 0) THEN amount END) as withdrawAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $withdrawalMonth->map(function ($withdrawData) use ($report) {
            if (!in_array($withdrawData->months, $report['months']->toArray())) {
                $report['months']->push($withdrawData->months);
            }
            $report['withdraw_month_amount']->push(getAmount($withdrawData->withdrawAmount));
        });

        //send Money statistics
        $sendingCountries = Country::whereHas('sendingTransfers', function ($query) {
            $query->completed();
        })->get(['id', 'currency']);

        $receivingCountries = Country::whereHas('receivingTransfers', function ($query) {
            $query->completed();
        })->get(['id', 'currency']);

        if ($sendingCountries->first() && $sendingCountries->first() == $receivingCountries->first()) {
            $firstReceivingCountry = $receivingCountries->shift();
            $secondReceivingCountry = $receivingCountries->shift();

            $receivingCountries->prepend($firstReceivingCountry);
            $receivingCountries->prepend($secondReceivingCountry);
        }

        $sendMoneyData = SendMoney::selectRaw('sending_country.id as sending_country_id, sending_country.currency as sending_currency, sending_country.name as sending_country, sending_country.image as sending_country_image, recipient_country.id as recipient_country_id, recipient_country.name as recipient_country, recipient_country.image as recipient_country_image, SUM(send_money.sending_amount) as total_amount, SUM(send_money.base_currency_amount) as total_base_amount')
            ->join('countries as sending_country', 'send_money.sending_country_id', '=', 'sending_country.id')
            ->join('countries as recipient_country', 'send_money.recipient_country_id', '=', 'recipient_country.id')
            ->groupBy('sending_country_id', 'recipient_country_id')
            ->where('send_money.created_at', '>=', now()->subYear())
            ->get();


        $sendMoneyAll = SendMoney::where('payment_status', Status::PAYMENT_SUCCESS)->SelectRaw('count(id) as total, status')->groupBy('status')->get();

        $sendMoneyLabels = [
            ['title' => 'Initiated', 'status' => 0],
            ['title' => 'Pending', 'status' => 2],
            ['title' => 'Completed', 'status' => 1],
            ['title' => 'Refunded', 'status' => 3]
        ];

        $sendMoneyStatistics = [];
        foreach ($sendMoneyLabels as $item) {
            $sendMoneyStatusWise = $sendMoneyAll->where('status', $item['status'])->first();
            if ($sendMoneyStatusWise) {
                $sendMoneyStatistics[$item['title']] = getAmount($sendMoneyStatusWise->total);
            } else {
                $sendMoneyStatistics[$item['title']] = 0;
            }
        }

        //send money statistics end
        $months = $report['months'];
        for ($i = 0; $i < $months->count(); ++$i) {
            $monthVal      = Carbon::parse($months[$i]);
            for ($j = $i + 1; $j < $months->count(); $j++) {
                if (isset($months[$j])) {
                    $dateValNext = Carbon::parse($months[$j]);
                    if ($dateValNext < $monthVal) {
                        $temp = $months[$i];
                        $months[$i]   = Carbon::parse($months[$j])->format('F-Y');
                        $months[$j] = Carbon::parse($temp)->format('F-Y');
                    } else {
                        $months[$i]   = Carbon::parse($months[$i])->format('F-Y');
                    }
                }
            }
        }

        return view('admin.dashboard', compact('pageTitle', 'widget', 'chart', 'deposit', 'payment', 'withdrawals', 'sendMoney', 'depositsMonth', 'withdrawalMonth', 'months', 'trxReport', 'plusTrx', 'minusTrx', 'sendingCountries', 'receivingCountries', 'sendMoneyStatistics', 'sendMoneyData'));
    }

    public function sendMoneyStatistics(Request $request) {

        $sendMoney = SendMoney::where('created_at', '>=', now()->subYear())->completed()->where('sending_currency', $request->sending_currency)->where('recipient_currency', $request->recipient_currency)->get();

        $allSendMoney = $sendMoney->mapWithKeys(function ($money) {
            $date = date_format($money->created_at, 'M-Y');
            return [
                $date => [
                    'sending_amount' => (float)$money->sending_amount,
                    'base_currency_amount' => (float) $money->base_currency_amount
                ]
            ];
        });

        return [
            'allSendMoney' => $allSendMoney
        ];
    }

    public function profile() {
        $pageTitle = 'Profile';
        $admin     = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request) {
        $this->validate($request, [
            'name'  => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ]);
        $admin = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old          = $admin->image;
                $admin->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $admin->name  = $request->name;
        $admin->email = $request->email;
        $admin->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }


    public function password() {
        $pageTitle = 'Password Setting';
        $admin     = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request) {
        $this->validate($request, [
            'old_password' => 'required',
            'password'     => 'required|min:5|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = bcrypt($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications() {
        $notifications = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $pageTitle     = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications'));
    }


    public function notificationRead($id) {
        $notification = AdminNotification::findOrFail($id);
        $notification->is_read = Status::YES;

        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function requestReport() {
        $arr['app_name']      = systemDetails()['name'];
        $arr['app_url']       = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $url             = "https://license.viserlab.com/issue/get?" . http_build_query($arr);
        $response        = CurlRequest::curlContent($url);
        $response        = json_decode($response);
        if ($response->status == 'error') {
            return to_route('admin.dashboard')->withErrors($response->message);
        }
        $reports   = $response->message[0];
        $pageTitle = 'Your Listed Report & Request';
        return view('admin.reports', compact('reports', 'pageTitle'));
    }

    public function reportSubmit(Request $request) {
        $request->validate([
            'type'    => 'required|in:bug,feature',
            'message' => 'required',
        ]);
        $url = 'https://license.viserlab.com/issue/add';

        $arr['app_name']      = systemDetails()['name'];
        $arr['app_url']       = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $arr['req_type']      = $request->type;
        $arr['message']       = $request->message;
        $response        = CurlRequest::curlPostContent($url, $arr);
        $response        = json_decode($response);

        if ($response->status == 'error') {
            return back()->withErrors($response->message);
        }

        $notify[] = ['success', $response->message];
        return back()->withNotify($notify);
    }

    public function readAll() {
        AdminNotification::where('is_read', Status::NO)->update([
            'is_read' => Status::YES
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function downloadAttachment($fileHash) {
        $filePath  = decrypt($fileHash);
        if (!(file_exists($filePath) && is_file($filePath))) {
            $notify[] = ['error', 'File not found!'];
            return back()->withNotify($notify);
        }
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title = slug(gs('site_name')).'- attachments.'.$extension;
        $mimetype  = mime_content_type($filePath);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }
}
