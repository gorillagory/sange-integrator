<?php

// routes/web.php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceRecordController;
use App\Http\Controllers\Admin\RbacController;
use App\Http\Controllers\System\BlueprintController;
use App\Http\Controllers\System\AuditLogController;
use App\Http\Controllers\System\CompanyController;
use App\Http\Controllers\System\RbacController as SystemRbacController;
use App\Http\Controllers\System\UserController;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\ResetPermissionTeam;
use App\Support\AppHost;
use Illuminate\Support\Facades\Route;

Route::domain(AppHost::systemHost())
    ->middleware(['web', 'auth', ResetPermissionTeam::class, 'role:super_admin|system_admin'])
    ->group(function () {
        Route::get('/lobby', function () {
            return Inertia\Inertia::render('Dashboard');
        })->name('central.lobby');

        Route::get('/dashboard', function () {
            return Inertia\Inertia::render('System/Dashboard');
        })->name('system.dashboard');

        Route::get('/blueprints', [BlueprintController::class, 'index'])->name('system.blueprints.index');
        Route::get('/blueprints/create', [BlueprintController::class, 'create'])->name('system.blueprints.create');
        Route::post('/blueprints', [BlueprintController::class, 'store'])->name('system.blueprints.store');
        Route::get('/blueprints/{id}/edit', [BlueprintController::class, 'edit'])->name('system.blueprints.edit');
        Route::put('/blueprints/{id}', [BlueprintController::class, 'update'])->name('system.blueprints.update');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('system.audit-logs.index');
        Route::get('/rbac', [SystemRbacController::class, 'index'])->name('system.rbac.index');
        Route::post('/rbac/roles', [SystemRbacController::class, 'store'])->name('system.rbac.roles.store');
        Route::put('/rbac/roles/{roleId}', [SystemRbacController::class, 'update'])->whereNumber('roleId')->name('system.rbac.roles.update');
        Route::delete('/rbac/roles/{roleId}', [SystemRbacController::class, 'destroy'])->whereNumber('roleId')->name('system.rbac.roles.destroy');
        Route::put('/rbac/members/{userId}/roles', [SystemRbacController::class, 'updateMemberRoles'])->whereNumber('userId')->name('system.rbac.members.roles.update');

        Route::middleware('role:super_admin')->group(function () {
            Route::get('/companies', [CompanyController::class, 'index'])->name('system.companies.index');
            Route::post('/companies', [CompanyController::class, 'store'])->name('system.companies.store');
            Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('system.companies.update');
            Route::put('/main-group-companies/{mainGroupCompany}', [CompanyController::class, 'updateMainGroupCompany'])->name('system.main-groups.update');

            Route::get('/users', [UserController::class, 'index'])->name('system.users.index');
            Route::post('/users', [UserController::class, 'store'])->name('system.users.store');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('system.users.update');
        });
    });

Route::domain('{subdomain}.'.AppHost::baseDomain())
    ->middleware(['web', 'auth', IdentifyTenant::class])
    ->group(function () {
        Route::get('/dashboard', function () {
            return Inertia\Inertia::render('TenantDashboard', [
                'company' => view()->shared('currentCompany'),
            ]);
        })->name('tenant.dashboard');

        Route::middleware(['company_module:travel.booking', 'global_admin_or_tenant_permission:service_records.view'])->group(function () {
            Route::get('/service-records', [ServiceRecordController::class, 'index'])->name('service-records.index');
            Route::get('/service-records/{id}/download-document', [ServiceRecordController::class, 'downloadDocument'])->whereNumber('id')->name('service-records.download');
            Route::get('/service-records/{id}/documents/{documentId}/download', [ServiceRecordController::class, 'downloadGeneratedDocument'])->whereNumber('id')->whereNumber('documentId')->name('service-records.documents.download');
            Route::get('/service-records/{id}', [ServiceRecordController::class, 'show'])->whereNumber('id')->name('service-records.show');

            Route::redirect('/operations', '/service-records')->name('operations.index');
            Route::get('/operations/{id}', [OperationController::class, 'show'])->name('operations.show');
            Route::put('/operations/{id}/document', [OperationController::class, 'updateDocument'])->name('operations.document');
            Route::get('/operations/{id}/download-document', [OperationController::class, 'downloadDocument'])->name('operations.download');
        });

        Route::middleware(['company_module:travel.booking', 'global_admin_or_tenant_permission:service_records.capture'])->group(function () {
            Route::get('/service-records/create', [ServiceRecordController::class, 'create'])->name('service-records.create');
            Route::post('/service-records', [ServiceRecordController::class, 'store'])->name('service-records.store');
            Route::get('/service-records/{id}/edit', [ServiceRecordController::class, 'edit'])->whereNumber('id')->name('service-records.edit');
            Route::put('/service-records/{id}', [ServiceRecordController::class, 'update'])->whereNumber('id')->name('service-records.update');

            Route::redirect('/operations/create', '/service-records/create')->name('operations.create');
            Route::post('/operations', [OperationController::class, 'store'])->name('operations.store');
        });

        Route::middleware(['company_module:travel.booking', 'global_admin_or_tenant_permission:service_records.status.manage'])->group(function () {
            Route::put('/service-records/{id}/service-status', [ServiceRecordController::class, 'updateServiceStatus'])->whereNumber('id')->name('service-records.service-status');
        });

        Route::middleware(['company_module:travel.booking', 'global_admin_or_tenant_permission:service_records.document.manage'])->group(function () {
            Route::put('/service-records/{id}/document', [ServiceRecordController::class, 'updateDocument'])->whereNumber('id')->name('service-records.document');
        });

        Route::middleware(['company_module:travel.booking', 'global_admin_or_tenant_permission:service_records.documents.generate'])->group(function () {
            Route::post('/service-records/{id}/documents', [ServiceRecordController::class, 'generateDocument'])->whereNumber('id')->name('service-records.documents.generate');
        });

        Route::middleware(['company_module:travel.booking', 'global_admin_or_tenant_permission:clients.view'])->group(function () {
            Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        });

        Route::middleware(['company_module:travel.booking', 'global_admin_or_tenant_permission:reports.view'])->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        });

        Route::middleware(['company_module:travel.booking', 'global_admin_or_tenant_permission:clients.manage'])->group(function () {
            Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
            Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
            Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
            Route::post('/clients/{client}/remark-presets', [ClientController::class, 'storeRemarkPreset'])->name('clients.remark-presets.store');
            Route::put('/clients/{client}/remark-presets/{preset}', [ClientController::class, 'updateRemarkPreset'])->name('clients.remark-presets.update');
            Route::delete('/clients/{client}/remark-presets/{preset}', [ClientController::class, 'destroyRemarkPreset'])->name('clients.remark-presets.destroy');

            Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
            Route::put('/contracts/{id}', [ContractController::class, 'update'])->name('contracts.update');
        });

        Route::middleware(['company_module:travel.schemas', 'global_admin_or_tenant_permission:schemas.view'])->group(function () {
            Route::get('/admin/schemas', [\App\Http\Controllers\Admin\SchemaController::class, 'index'])->name('admin.schemas.index');
            Route::get('/admin/schemas/create', [\App\Http\Controllers\Admin\SchemaController::class, 'create'])->name('admin.schemas.create');
            Route::get('/admin/schemas/{id}/edit', [\App\Http\Controllers\Admin\SchemaController::class, 'edit'])->name('admin.schemas.edit');
        });

        Route::middleware(['company_module:travel.schemas', 'global_admin_or_tenant_permission:schemas.manage'])->group(function () {
            Route::post('/admin/schemas', [\App\Http\Controllers\Admin\SchemaController::class, 'store'])->name('admin.schemas.store');
            Route::put('/admin/schemas/{id}', [\App\Http\Controllers\Admin\SchemaController::class, 'update'])->name('admin.schemas.update');
            Route::delete('/admin/schemas/{id}', [\App\Http\Controllers\Admin\SchemaController::class, 'destroy'])->name('admin.schemas.destroy');
        });

        Route::middleware(['company_module:travel.documents', 'global_admin_or_tenant_permission:documents.view'])->group(function () {
            Route::get('/admin/documents', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'index'])->name('admin.documents.index');
            Route::get('/admin/documents/create', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'create'])->name('admin.documents.create');
            Route::get('/admin/documents/{id}/edit', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'edit'])->name('admin.documents.edit');
            Route::get('/admin/documents/{subdomain}/{id}/preview', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'preview'])->name('admin.documents.preview');
        });

        Route::middleware(['company_module:travel.documents', 'global_admin_or_tenant_permission:documents.manage'])->group(function () {
            Route::post('/admin/documents', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'store'])->name('admin.documents.store');
            Route::post('/admin/documents/preview-html', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'previewHtml'])->name('admin.documents.preview-html');
            Route::put('/admin/documents/{id}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'update'])->name('admin.documents.update');
            Route::delete('/admin/documents/{id}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'destroy'])->name('admin.documents.destroy');
        });

        Route::middleware(['global_admin_or_tenant_permission:rbac.view'])->group(function () {
            Route::get('/admin/rbac', [RbacController::class, 'index'])->name('admin.rbac.index');
        });

        Route::middleware(['global_admin_or_tenant_permission:rbac.manage'])->group(function () {
            Route::post('/admin/rbac/roles', [RbacController::class, 'store'])->name('admin.rbac.roles.store');
            Route::put('/admin/rbac/roles/{roleId}', [RbacController::class, 'update'])->whereNumber('roleId')->name('admin.rbac.roles.update');
            Route::delete('/admin/rbac/roles/{roleId}', [RbacController::class, 'destroy'])->whereNumber('roleId')->name('admin.rbac.roles.destroy');
            Route::put('/admin/rbac/members/{userId}/roles', [RbacController::class, 'updateMemberRoles'])->whereNumber('userId')->name('admin.rbac.members.roles.update');
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
