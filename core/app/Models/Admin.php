<?php
    // Este modelo representa a un administrador en la aplicación.
    // Extiende de Authenticatable para manejar la autenticación.

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // Estos atributos se ocultan cuando se convierte el modelo a un array o JSON.
    protected $hidden = [
        'password', 'remember_token',
    ];

}
