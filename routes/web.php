<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'login'])->name('login1');
Route::get('/login', [LoginController::class, 'login'])->name('Login');

