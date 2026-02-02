<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuickLoginController;

Route::get('/', [QuickLoginController::class, 'index'])->name('portal');
use App\Http\Controllers\AuthRedirectController;

Route::get('/', [QuickLoginController::class, 'index'])->name('portal');
Route::get('/secretary', [AuthRedirectController::class, 'secretaryLogin'])->name('secretary.login');
Route::get('/login', [AuthRedirectController::class, 'login'])->name('login');

Route::post('/logout', [AuthRedirectController::class, 'logout'])->name('logout');
// GET Logout for Suspended and general fallback
Route::get('/logout-suspended', [AuthRedirectController::class, 'logout']);
Route::get('/logout', [AuthRedirectController::class, 'logout']);


