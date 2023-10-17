<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Authenticatable {
    // Este modelo representa a un agente en la aplicación.
    // Extiende de Authenticatable para manejar la autenticación y utiliza HasApiTokens para la autenticación de API.
    use HasApiTokens;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // Estos atributos se ocultan cuando se convierte el modelo a un array o JSON.
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    // Estos atributos se convierten a tipos nativos cuando se accede a ellos.
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address' => 'object',
        'kyc_data' => 'object',
        'ver_code_send_at' => 'datetime'
    ];


    public function country() {
    // Este método define una relación 'belongsTo' con el modelo Country.
    // Indica que cada agente pertenece a un país.
        return $this->belongsTo(Country::class);
    }

    public function loginLogs() {
    // Este método define una relación 'hasMany' con el modelo UserLogin.
    // Indica que un agente puede tener múltiples registros de inicio de sesión.
        return $this->hasMany(UserLogin::class);
    }

    public function transactions() {
    // Este método define una relación 'hasMany' con el modelo Transaction.
    // Indica que un agente puede tener múltiples transacciones.
        return $this->hasMany(Transaction::class)->orderBy('id', 'desc');
    }

    public function deposits() {
    // Este método define una relación 'hasMany' con el modelo Deposit.
    // Indica que un agente puede tener múltiples depósitos.
        return $this->hasMany(Deposit::class)->where('status', '!=', Status::PAYMENT_SUCCESS);
    }

    public function withdrawals() {
    // Este método define una relación 'hasMany' con el modelo Withdrawal.
    // Indica que un agente puede tener múltiples retiros.
        return $this->hasMany(Withdrawal::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function fullname(): Attribute {
    // Este método devuelve el nombre completo del agente concatenando el nombre y apellido.
        return new Attribute(
            get: fn () => $this->firstname . ' ' . $this->lastname,
        );
    }

    // SCOPES
    // Los siguientes métodos son 'scopes' que permiten filtrar consultas de modelos de manera más fácil.
    public function scopeActive($query) {
        $query->where('status', Status::ENABLE);
    }

    public function scopeBanned($query) {
        $query->where('status', Status::DISABLE);
    }


    public function scopeKycUnverified($query) {
        $query->where('kv', Status::KYC_UNVERIFIED);
    }

    public function scopeKycPending($query) {
        $query->where('kv', Status::KYC_PENDING);
    }


    public function scopeWithBalance($query) {
        $query->where('balance', '>', 0);
    }
}
