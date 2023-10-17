<?php
// Este archivo define todos los comandos de consola basados en Closures que tu aplicación soporta.

use Illuminate\Foundation\Inspiring;
// Importamos la clase Inspiring para mostrar citas inspiradoras.
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    // Este comando muestra una cita inspiradora cuando se ejecuta 'php artisan inspire'.
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
