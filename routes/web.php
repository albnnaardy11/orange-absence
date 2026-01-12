<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->hasAnyRole(['super_admin', 'secretary'])) {
            return redirect()->to('/admin');
        }
        return redirect()->to('/member');
    }
    return view('welcome');
});

Route::get('/login', function () {
    return redirect()->to('/member/login');
})->name('login');
