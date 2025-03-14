<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('schedule', function () {
    return Inertia::render('schedule/Schedule');
})->middleware(['auth', 'verified'])->name('schedule');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
