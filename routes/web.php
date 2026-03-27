<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::redirect('dashboard', 'admin');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'dashboard')
        ->middleware(['auth', 'role:superadmin,admin'])
        ->name('dashboard');

    Volt::route('users', 'admin.users.index')
        ->middleware(['auth', 'role:superadmin'])
        ->name('users.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';