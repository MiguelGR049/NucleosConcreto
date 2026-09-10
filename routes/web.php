<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\PropiedadesController;
use App\Http\Controllers\EnsayoController;
use App\Http\Controllers\ConsultaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'login'])->name('login');
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'login_usuario'])->name('login.post');
Route::post('/cerrar-sesion', [LoginController::class, 'cerrar_sesion'])->name('cerrar_sesion');

Route::get('/inicio', [HomeController::class, 'inicio'])->name('inicio');

Route::get('/recepcion', [RecepcionController::class, 'crear'])->name('recepcion');

Route::get('/capturas', [PropiedadesController::class, 'capturas'])->name('capturas');

Route::get('/ensayo', [EnsayoController::class, 'ensayo'])->name('ensayo');

Route::get('/consulta', [ConsultaController::class, 'consulta'])->name('consulta');