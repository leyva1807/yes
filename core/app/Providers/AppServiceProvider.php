<?php

namespace App\Providers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Agent;
use App\Models\Deposit;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\SendMoney;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() 
    {
        
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $general = gs();
        $activeTemplate = activeTemplate();
        $viewShare['general'] = $general;
        $viewShare['activeTemplate'] = $activeTemplate;
        $viewShare['activeTemplateTrue'] = activeTemplate(true);
        $viewShare['language'] = Language::all();
        $viewShare['emptyMessage'] = 'Data not found';
        view()->share($viewShare);


        view()->composer('admin.partials.sidenav', function ($view) {
            $view->with([
                'bannedUsersCount'            => User::banned()->count(),
                'bannedAgentsCount'           => Agent::banned()->count(),
                'emailUnverifiedUsersCount'   => User::emailUnverified()->count(),
                'mobileUnverifiedUsersCount'  => User::mobileUnverified()->count(),
                'kycUnverifiedUsersCount'     => User::kycUnverified()->count(),
                'kycPendingUsersCount'        => User::kycPending()->count(),
                'kycUnverifiedAgentsCount'    => Agent::kycUnverified()->count(),
                'kycPendingAgentsCount'       => Agent::kycPending()->count(),
                'pendingTicketCount'          => SupportTicket::whereIN('status', [Status::TICKET_OPEN, Status::TICKET_REPLY])->count(),
                'pendingDepositsCount'        => Deposit::pending()->where('user_id', 0)->count(),
                'pendingPaymentsCount'        => Deposit::pending()->where('user_id', '!=', 0)->count(),
                'pendingWithdrawCount'        => Withdrawal::pending()->count(),
                'shouldPayoutCount'           => SendMoney::where('status', Status::SEND_MONEY_PENDING)->count()
            ]);
        });

        view()->composer('admin.partials.topnav', function ($view) {
            $view->with([
                'adminNotifications' => AdminNotification::where('is_read', Status::NO)->with(['user', 'agent'])->orderBy('id', 'desc')->take(10)->get(),
                'adminNotificationCount' => AdminNotification::where('is_read', Status::NO)->count(),
            ]);
        });

        view()->composer('partials.seo', function ($view) {
            $seo = Frontend::where('data_keys', 'seo.data')->first();
            $view->with([
                'seo' => $seo ? $seo->data_values : $seo,
            ]);
        });

        if ($general->force_ssl) {
            \URL::forceScheme('https');
        }


        Paginator::useBootstrapFour();
    }
}
