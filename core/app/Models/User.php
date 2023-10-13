<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Searchable;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'ver_code', 'balance', 'kyc_data'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address' => 'object',
        'ver_code_send_at' => 'datetime',
        'kyc_data' => 'object',
        'business_profile_data' => 'object',
    ];

    public function referrer()
    {
        return $this->belongsTo(self::class, 'ref_by');
    }

    public function loginLogs()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('id', 'desc');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function fullname(): Attribute
    {
        return new Attribute(
            get: fn () => $this->firstname . ' ' . $this->lastname,
        );
    }

    // SCOPES
    public function scopeActive($query)
    {
        $query->where('status', Status::USER_ACTIVE)->where('ev', Status::VERIFIED)->where('sv', Status::VERIFIED);
    }

    public function scopeBanned($query)
    {
        $query->where('status', Status::USER_BAN);
    }

    public function scopeEmailUnverified($query)
    {
        $query->where('ev', Status::NO);
    }

    public function scopeKycUnverified($query)
    {
        $query->where('kv', Status::NO);
    }

    public function scopeKycPending($query)
    {
        $query->where('kv', Status::KYC_PENDING);
    }

    public function scopeMobileUnverified($query)
    {
        $query->where('sv', Status::NO);
    }


    public function scopeEmailVerified($query)
    {
        $query->where('ev', Status::VERIFIED);
    }

    public function scopeMobileVerified($query)
    {
        $query->where('sv', Status::VERIFIED);
    }

    public function scopeWithBalance($query)
    {
        $query->where('balance', '>', 0);
    }

    public function scopePersonalAccount($query)
    {
        $query->where('status', Status::USER_ACTIVE)->where('ev', Status::VERIFIED)->where('sv', Status::VERIFIED)->where('type', 0);
    }
    public function scopeBusinessAccount($query)
    {
        $query->where('status', Status::USER_ACTIVE)->where('ev', Status::VERIFIED)->where('sv', Status::VERIFIED)->where('type', 1);
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(function () {
            $html = '';
            if ($this->type == 0) {
                $html = '<span class="badge badge--primary">' . trans('Personal') . '</span>';
            } elseif ($this->type == 1) {
                $html = '<span><span class="badge badge--success">' . trans('Business') . '</span>';
            }
            return $html;
        });
    }

    public function isAuthorized($user)
    {
        if ($user->ev && $user->sv && $user->tv) {
            return true;
        }
        return false;
    }
}
