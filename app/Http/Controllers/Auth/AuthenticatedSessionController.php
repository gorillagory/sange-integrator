<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditEngine;
use App\Services\AuthRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, AuthRedirectService $redirectService): RedirectResponse|HttpResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = $request->user();

        $targetUrl = $redirectService->redirectAfterLogin($request, $user);

        AuditEngine::log('AUTH', 'AUTH.LOGIN_SUCCESS', [
            'target_url' => $targetUrl,
            'is_inertia' => (bool) $request->header('X-Inertia'),
        ], [], $user);

        // Inertia XHR cannot follow cross-origin redirects between subdomains.
        if ($request->header('X-Inertia') && $this->isCrossOriginRedirect($request, $targetUrl)) {
            return Inertia::location($targetUrl);
        }

        return redirect()->intended($targetUrl);
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()) {
            AuditEngine::log('AUTH', 'AUTH.LOGOUT', [], [], $request->user());
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function isCrossOriginRedirect(Request $request, string $targetUrl): bool
    {
        $target = parse_url($targetUrl);

        if (! is_array($target) || ! isset($target['host'])) {
            return false;
        }

        $targetScheme = $target['scheme'] ?? $request->getScheme();
        $targetHost = $target['host'];
        $targetPort = $target['port'] ?? null;

        $requestScheme = $request->getScheme();
        $requestHost = $request->getHost();
        $requestPort = $request->getPort();

        return $targetScheme !== $requestScheme
            || $targetHost !== $requestHost
            || ($targetPort !== null && $targetPort !== $requestPort);
    }
}
