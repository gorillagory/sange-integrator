<?php

use App\Http\Controllers\ProfileController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. THE SYSTEM CORE (Control DB)
// ==========================================
Route::domain('sys.bayam.test')->group(function () {

    Route::get('/', function () {
        return view('welcome');
    });

    // THIS is the Launchpad that queries the companies!
    Route::get('/dashboard', function () {
        $user = Auth::user();

        // Fetch all companies this user is allowed to see
        $companies = \Illuminate\Support\Facades\DB::connection('control')->table('companies')
            ->join('company_user', 'companies.id', '=', 'company_user.company_id')
            ->where('company_user.user_id', $user->id)
            ->where('companies.is_active', true)
            ->select('companies.*', 'company_user.role')
            ->get();

        return view('dashboard', ['companies' => $companies]);
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Binds authentication strictly to the sys domain
    require __DIR__.'/auth.php';
});

// ==========================================
// 2. THE TENANT OPERATIONS (Dynamic DB)
// ==========================================
Route::domain('{subdomain}.bayam.test')->middleware(['web', 'auth', IdentifyTenant::class])->group(function () {

    // THIS is the isolated vault view!
    Route::get('/dashboard', function ($subdomain) {
        $company = view()->shared('currentCompany');
        $dbName = config('database.connections.tenant.database');

        return "
            <div style='font-family: sans-serif; padding: 2rem; background: #f3f4f6; min-height: 100vh;'>
                <div style='background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto;'>
                    <h1 style='color: #1f2937;'>Welcome to {$company->name}</h1>
                    <p style='color: #4b5563;'>You have securely entered the isolated operational vault.</p>
                    <hr style='margin: 1rem 0; border: none; border-top: 1px solid #e5e7eb;' />
                    <ul style='color: #6b7280; font-family: monospace;'>
                        <li><strong>Subdomain:</strong> {$subdomain}</li>
                        <li><strong>Active Database:</strong> {$dbName}</li>
                        <li><strong>Connection:</strong> Physically Air-Gapped</li>
                    </ul>
                    <br>
                    <a href='http://sys.bayam.test:8000/dashboard' style='color: #3b82f6; text-decoration: none;'>&larr; Return to System Core</a>
                </div>
            </div>
        ";
    })->name('tenant.dashboard');

});
