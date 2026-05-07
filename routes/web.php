<?php

// routes/web.php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\System\BlueprintController;
use App\Http\Controllers\System\CompanyController;
use App\Http\Controllers\System\UserController;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\ResetPermissionTeam;
use Illuminate\Support\Facades\Route;

Route::domain('sys.bayam.test')
    ->middleware(['web', 'auth', ResetPermissionTeam::class, 'role:super_admin|system_admin'])
    ->group(function () {
        Route::get('/lobby', function () {
            return Inertia\Inertia::render('Dashboard');
        })->name('central.lobby');

        Route::get('/dashboard', function () {
            return Inertia\Inertia::render('System/Dashboard');
        })->name('system.dashboard');

        Route::get('/companies', [CompanyController::class, 'index'])->name('system.companies.index');
        Route::post('/companies', [CompanyController::class, 'store'])->name('system.companies.store');

        Route::get('/blueprints', [BlueprintController::class, 'index'])->name('system.blueprints.index');
        Route::get('/blueprints/create', [BlueprintController::class, 'create'])->name('system.blueprints.create');
        Route::post('/blueprints', [BlueprintController::class, 'store'])->name('system.blueprints.store');
        Route::get('/blueprints/{id}/edit', [BlueprintController::class, 'edit'])->name('system.blueprints.edit');
        Route::put('/blueprints/{id}', [BlueprintController::class, 'update'])->name('system.blueprints.update');

        Route::get('/users', [UserController::class, 'index'])->name('system.users.index');
        Route::post('/users', [UserController::class, 'store'])->name('system.users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('system.users.update');
    });

Route::domain('{subdomain}.bayam.test')
    ->middleware(['web', 'auth', IdentifyTenant::class])
    ->group(function () {
        Route::get('/dashboard', function () {
            return Inertia\Inertia::render('TenantDashboard', [
                'company' => view()->shared('currentCompany'),
            ]);
        })->name('tenant.dashboard');

        Route::middleware(['company_module:travel.booking'])->group(function () {
            Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
            Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
            Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
            Route::get('/bookings/{id}/download-invoice', [BookingController::class, 'downloadInvoice'])->name('bookings.download');
            Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
            Route::put('/bookings/{id}/invoice', [BookingController::class, 'updateInvoice'])->name('bookings.invoice');
        });

        Route::middleware(['company_module:travel.booking'])->group(function () {
            Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
            Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
            Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

            Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
            Route::put('/contracts/{id}', [ContractController::class, 'update'])->name('contracts.update');

            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        });

        Route::middleware(['company_module:travel.schemas', 'super_admin_or_tenant_role:agency_admin'])->group(function () {
            Route::get('/admin/schemas', [\App\Http\Controllers\Admin\SchemaController::class, 'index'])->name('admin.schemas.index');
            Route::get('/admin/schemas/create', [\App\Http\Controllers\Admin\SchemaController::class, 'create'])->name('admin.schemas.create');
            Route::post('/admin/schemas', [\App\Http\Controllers\Admin\SchemaController::class, 'store'])->name('admin.schemas.store');
            Route::get('/admin/schemas/{id}/edit', [\App\Http\Controllers\Admin\SchemaController::class, 'edit'])->name('admin.schemas.edit');
            Route::put('/admin/schemas/{id}', [\App\Http\Controllers\Admin\SchemaController::class, 'update'])->name('admin.schemas.update');
            Route::delete('/admin/schemas/{id}', [\App\Http\Controllers\Admin\SchemaController::class, 'destroy'])->name('admin.schemas.destroy');
        });

        Route::middleware(['company_module:travel.documents', 'super_admin_or_tenant_role:agency_admin,document_manager'])->group(function () {
            Route::get('/admin/documents', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'index'])->name('admin.documents.index');
            Route::get('/admin/documents/create', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'create'])->name('admin.documents.create');
            Route::post('/admin/documents', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'store'])->name('admin.documents.store');
            Route::get('/admin/documents/{id}/edit', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'edit'])->name('admin.documents.edit');
            Route::put('/admin/documents/{id}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'update'])->name('admin.documents.update');
            Route::delete('/admin/documents/{id}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'destroy'])->name('admin.documents.destroy');
            Route::get('/admin/documents/{subdomain}/{id}/preview', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'preview'])->name('admin.documents.preview');
        });
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';
