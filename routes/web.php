<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::redirect('dashboard', 'admin');

Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\SetAdminLocale::class)->group(function () {
    Route::view('/', 'dashboard')
        ->middleware(['auth', 'role:superadmin,admin'])
        ->name('dashboard');

    Volt::route('users', 'admin.users.index')
        ->middleware(['auth', 'role:superadmin,admin'])
        ->name('users.index');
    
    Volt::route('flats', 'admin.flats.index')
            ->middleware(['auth', 'role:superadmin,admin'])
            ->name('flats.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';