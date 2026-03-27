<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('admin/login', 'auth.login')
        ->name('login');

    Volt::route('admin/forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Volt::route('admin/reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('admin/verify-email', 'auth.verify-email')
        ->name('verification.notice');

    Route::get('admin/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('admin/confirm-password', 'auth.confirm-password')
        ->name('password.confirm');
});

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');