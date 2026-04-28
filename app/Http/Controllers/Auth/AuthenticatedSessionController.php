<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // 🌟 ENTERPRISE DYNAMIC REDIRECT
        // Detect which domain the user just logged into
        $host = $request->getHost();

        if ($host === 'sys.bayam.test') {
            // If they logged into the System layer, route to the Genesis Vault
            return redirect()->intended(route('system.dashboard'));
        }

        // If they logged into a Tenant layer (e.g., bt.bayam.test), route to their specific POS Vault
        $subdomain = explode('.', $host)[0];
        return redirect()->intended(route('tenant.dashboard', ['subdomain' => $subdomain]));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
