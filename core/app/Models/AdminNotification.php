<?php
    // Este modelo representa una notificación para el administrador en la aplicación.
    // Extiende de Model para manejar las operaciones de la base de datos.
    // Este método define una relación 'belongsTo' con el modelo User.
    // Indica que cada notificación pertenece a un usuario.
    // Este método define una relación 'belongsTo' con el modelo Agent.
    // Indica que cada notificación pertenece a un agente.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
