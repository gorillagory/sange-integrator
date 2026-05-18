<?php

namespace App\Http\Middleware;

use App\Support\AppHost;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSessionDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = AppHost::normalizeHost((string) $request->getHost());
        $baseDomain = AppHost::baseDomain();
        $configuredDomain = trim((string) config('session.domain', ''));

        $resolvedDomain = null;

        if ($host === $baseDomain || str_ends_with($host, '.'.$baseDomain)) {
            $resolvedDomain = $configuredDomain !== ''
                ? $configuredDomain
                : AppHost::defaultSessionDomain();
        }

        config(['session.domain' => $resolvedDomain]);

        return $next($request);
    }
}
