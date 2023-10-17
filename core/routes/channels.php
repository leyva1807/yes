<?php
// Este archivo define todos los canales de transmisión que tu aplicación soporta.

use Illuminate\Support\Facades\Broadcast;
// Importamos la clase Broadcast para definir los canales de transmisión.

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
|  you may register all of the event broadcasting channels that your
| application suHerepports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    // Este canal de transmisión se utiliza para autorizar a los usuarios en función de su ID.
    return (int) $user->id === (int) $id;
});
