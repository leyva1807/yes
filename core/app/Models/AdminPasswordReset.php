<?php
    // Este modelo representa un registro de restablecimiento de contraseña para los administradores.
    // Extiende de Model para manejar las operaciones de la base de datos.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPasswordReset extends Model
{
    protected $table      = "admin_password_resets";
    // Define la tabla de la base de datos que se utilizará para este modelo.
    protected $guarded    = ['id'];
    // Define los atributos que no deben ser asignables masivamente.
    public    $timestamps = false;
    // Indica que este modelo no debe manejar marcas de tiempo.
}
