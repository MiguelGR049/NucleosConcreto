<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\PropiedadesController;
use App\Http\Controllers\EnsayoController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\EdicionController;
use App\Http\Controllers\UsuariosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'login'])->name('login');
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'login_usuario'])->name('login.post');
Route::post('/cerrar-sesion', [LoginController::class, 'cerrar_sesion'])->name('cerrar_sesion');

Route::get('/inicio', [HomeController::class, 'inicio'])->name('inicio');

Route::get('/recepcion', [RecepcionController::class, 'crear'])->name('recepcion');
Route::get('/recepcion/obra', [RecepcionController::class, 'buscarObra'])->name('recepcion.obra');
Route::post('/recepcion', [RecepcionController::class, 'store'])->name('recepcion.store');

Route::get('/propiedades', [PropiedadesController::class, 'capturas'])->name('propiedades');
Route::post('/propiedades', [PropiedadesController::class, 'guardar'])->name('propiedades.guardar');

Route::get('/ensayo', [EnsayoController::class, 'ensayo'])->name('ensayo');
Route::post('/ensayo', [EnsayoController::class, 'guardar'])->name('ensayo.guardar');

Route::get('/consulta', [ConsultaController::class, 'consulta'])->name('consulta');
Route::post('/consulta/pdf', [ConsultaController::class, 'pdf'])->name('consulta.pdf');

Route::middleware('admin')->group(function () {
    Route::get('/correccion', [EdicionController::class, 'edicion'])->name('correccion');
    Route::post('/correccion', [EdicionController::class, 'guardar'])->name('correccion.guardar');
    Route::post('/correccion/eliminar', [EdicionController::class, 'eliminar'])->name('correccion.eliminar');
});

Route::middleware('ti')->group(function () {
    Route::get('/usuario', [UsuariosController::class, 'usuario'])->name('usuario');
    Route::post('/usuario', [UsuariosController::class, 'guardarUsuario'])->name('usuario.guardar');
    Route::post('/usuario/editar', [UsuariosController::class, 'editarUsuario'])->name('usuario.editar');
    Route::post('/usuario/eliminar', [UsuariosController::class, 'eliminarUsuario'])->name('usuario.eliminar');
});