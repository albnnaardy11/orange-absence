<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuickLoginController;

Route::get('/', [QuickLoginController::class, 'index'])->name('portal');
Route::get('/secretary', function () {
    return redirect()->to('/admin/login');
})->name('secretary.login');

Route::get('/login', function () {
    return redirect()->to('/admin/login');
})->name('login');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');


