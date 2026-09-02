<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'login'])->name('Login');
Route::get('/login', [LoginController::class, 'login'])->name('Login');

Route::get('/home', [HomeController::class, 'home'])->name('Home');