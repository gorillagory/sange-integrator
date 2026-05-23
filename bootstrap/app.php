<?php

// bootstrap/app.php

use App\Http\Middleware\AllowSuperAdminOrTenantRole;
use App\Http\Middleware\AllowGlobalAdminOrTenantPermission;
use App\Http\Middleware\EnsureCompanyModuleEnabled;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveSessionDomain;
use App\Http\Middleware\UseRequestHostForViteDevServer;
use App\Support\AppHost;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(
            fn (Request $request) => AppHost::absoluteUrlForRequest(
                $request,
                $request->getHost(),
                '/login',
            )
        );

        $middleware->web(prepend: [
            UseRequestHostForViteDevServer::class,
            ResolveSessionDomain::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'super_admin_or_tenant_role' => AllowSuperAdminOrTenantRole::class,
            'global_admin_or_tenant_permission' => AllowGlobalAdminOrTenantPermission::class,
            'company_module' => EnsureCompanyModuleEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
