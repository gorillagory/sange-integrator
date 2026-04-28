<?php

use App\Http\Controllers\ContractController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\System\CompanyController;
use App\Http\Controllers\System\BlueprintController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;

// ==========================================
// ZONE 1: THE SYSTEM DOMAIN (sys.bayam.test)
// MUST BE AT THE TOP!
// ==========================================
Route::domain('sys.bayam.test')->middleware(['web', 'auth'])->group(function () {

    // 🚪 The User Lobby
    Route::get('/lobby', function () {
        return Inertia\Inertia::render('Dashboard');
    })->name('central.lobby');

    // 🌌 The System Pulse
    Route::get('/dashboard', function () {
        return Inertia\Inertia::render('System/Dashboard');
    })->name('system.dashboard');

    // 🏢 The Genesis Roster
    Route::get('/companies', [CompanyController::class, 'index'])->name('system.companies.index');
    Route::post('/companies', [CompanyController::class, 'store'])->name('system.companies.store');

    // 🧬 The Blueprint Forge
    Route::get('/blueprints', [BlueprintController::class, 'index'])->name('system.blueprints.index');
    Route::get('/blueprints/create', [BlueprintController::class, 'create'])->name('system.blueprints.create'); // 👈 NEW
    Route::post('/blueprints', [BlueprintController::class, 'store'])->name('system.blueprints.store'); // 👈 NEW
    Route::get('/blueprints/{id}/edit', [BlueprintController::class, 'edit'])->name('system.blueprints.edit');
    Route::put('/blueprints/{id}', [BlueprintController::class, 'update'])->name('system.blueprints.update');
});

// ==========================================
// ZONE 2: THE TENANT DOMAIN (*.bayam.test)
// MUST BE AFTER THE SYSTEM DOMAIN!
// ==========================================
Route::domain('{subdomain}.bayam.test')->middleware(['web', 'auth', IdentifyTenant::class])->group(function () {

    // 📊 Tenant Dashboard (Light Theme - Inside the Vault)
    Route::get('/dashboard', function ($subdomain) {
        return Inertia\Inertia::render('TenantDashboard', [
            'company' => view()->shared('currentCompany')
        ]);
    })->name('tenant.dashboard');

    // ✈️ Bookings Module
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{id}/download-invoice', [BookingController::class, 'downloadInvoice'])->name('bookings.download');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::put('/bookings/{id}/invoice', [BookingController::class, 'updateInvoice'])->name('bookings.invoice');
    // 🏢 Clients Module
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

    // 📄 Contracts Module (NEW)
    Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
    Route::put('/contracts/{id}', [ContractController::class, 'update'])->name('contracts.update');
    // 📈 Reports Module
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Add this inside your authenticated route group:
    Route::get('/admin/schemas', function () {
        // If you haven't created the SchemaController yet, you can render it directly for now:
        return \Inertia\Inertia::render('Admin/Schemas/Builder');
    })->name('admin.schemas.builder');
});

// ==========================================
// ZONE 3: AUTHENTICATION & ROOT
// ==========================================
Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';
