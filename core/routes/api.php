<?php
// Este archivo define todas las rutas relacionadas con la API.

use Illuminate\Support\Facades\Route;
// Importamos la clase Route para definir las rutas.

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api')->name('api.')->group(function () {
    // Este grupo de rutas está bajo el espacio de nombres 'Api' y tiene un prefijo de nombre 'api.'.
});
