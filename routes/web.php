<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('inbox', fn () => Inertia::render('Inbox'))->name('inbox');
    Route::get('customers', fn () => Inertia::render('Customers'))->name('customers');
    Route::resource('users', \App\Http\Controllers\UserController::class, [
        'parameters' => ['users' => 'user:uuid'],
    ])->except(['create', 'show', 'edit']);
    Route::post('users/bulk-delete', [\App\Http\Controllers\UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
