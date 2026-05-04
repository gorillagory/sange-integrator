<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $host = $request->getHost();

        if ($host === 'sys.bayam.test') {
            return redirect()->intended(route('system.dashboard'));
        }

        if ($host === 'bayam.test' || $host === 'www.bayam.test') {
            return redirect()->intended('http://sys.bayam.test:8000/dashboard');
        }

        $parts = explode('.', $host);
        $subdomain = $parts[0] ?? null;

        if (! $subdomain || $subdomain === 'www' || $subdomain === 'bayam') {
            return redirect()->intended('http://sys.bayam.test:8000/dashboard');
        }

        return redirect()->intended(route('tenant.dashboard', [
            'subdomain' => $subdomain,
        ]));
    }

    public function destroy(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
