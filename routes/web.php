<?php

use Illuminate\Support\Facades\Route;

// 1. The System Core (Login/Admin)
Route::domain('sys.bayam.test')->group(function () {
    Route::get('/', function () { return 'Welcome to Sys Core (sys.bayam.test)'; });
    // Route::get('/login', [AuthController::class, 'show']);
});

// 2. Bayamedic
Route::domain('bner.bayam.test')->group(function () {
    Route::get('/', function () { return 'Welcome to Bayamedic (bner.bayam.test)'; });
});

// 3. Bayam Travel
Route::domain('bt.bayam.test')->group(function () {
    Route::get('/', function () { return 'Welcome to Bayam Travel (bt.bayam.test)'; });
});

// 4. Bayam Enterprise
Route::domain('enterprise.bayam.test')->group(function () {
    Route::get('/', function () { return 'Welcome to Bayam Enterprise (enterprise.bayam.test)'; });
});

// 5. Bayam Tech
Route::domain('btech.bayam.test')->group(function () {
    Route::get('/', function () { return 'Welcome to Bayam Tech (btech.bayam.test)'; });
});

// Fallback / Info Panel
Route::domain('info.bayam.test')->group(function () {
    Route::get('/', function () { return 'Welcome to Info Panel (info.bayam.test)'; });
});
