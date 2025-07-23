<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'mfa'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
});
