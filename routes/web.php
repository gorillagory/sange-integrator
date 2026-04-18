<?php
use Inertia\Inertia;

use App\Http\Controllers\ProfileController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


// ==========================================
// 0. THE GLOBAL DIAGNOSTIC PROBE
// ==========================================
Route::get('/diagnostic-global', function () {
    return response()->json([
        '0_Host' => request()->getHost(),
        '1_Session_ID' => session()->getId(),
        '2_Auth_Check' => \Illuminate\Support\Facades\Auth::check(),
        '3_User_ID' => \Illuminate\Support\Facades\Auth::id(),
        '4_Raw_Browser_Cookies' => request()->header('cookie'),
    ]);
})->middleware('web');
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

    Route::get('/dashboard', function ($subdomain) {
        // Grab the variables
        $company = view()->shared('currentCompany');
        $dbName = config('database.connections.tenant.database');

        // Render the Vue Component instead of a Blade view!
        return Inertia::render('TenantDashboard', [
            'company' => $company,
            'subdomain' => $subdomain,
            'dbName' => $dbName
        ]);
    })->name('tenant.dashboard');
//    Route::get('/diagnostic', function ($subdomain) {
//        return response()->json([
//            'Subdomain' => $subdomain,
//            'Is_Logged_In' => \Illuminate\Support\Facades\Auth::check(),
//            'User_Data' => \Illuminate\Support\Facades\Auth::user(),
//            'Cookie_Received' => request()->hasCookie(config('session.cookie'))
//        ]);
//    });
});

// ==========================================
// 3. THE NAKED DIAGNOSTIC ROUTE
// ==========================================
Route::domain('{subdomain}.bayam.test')->middleware('web')->group(function () {
    Route::get('/diagnostic-naked', function ($subdomain) {
        return response()->json([
            '1_Subdomain' => $subdomain,
            '2_Session_Domain_Config' => config('session.domain'),
            '3_Cookie_Received_By_Server' => request()->hasCookie(config('session.cookie')),
            '4_Is_Logged_In' => \Illuminate\Support\Facades\Auth::check(),
            '5_User_ID' => \Illuminate\Support\Facades\Auth::id(),
            '6_Has_Access' => \Illuminate\Support\Facades\DB::connection('control')
                ->table('company_user')
                ->where('user_id', \Illuminate\Support\Facades\Auth::id() ?? 0)
                ->whereIn('company_id', function($query) use ($subdomain) {
                    $query->select('id')->from('companies')->where('subdomain', $subdomain);
                })->exists()
        ]);
    });
});
