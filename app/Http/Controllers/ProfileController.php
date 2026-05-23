<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\UserIdentityService;
use App\Services\AuditEngine;
use App\Support\AppHost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserIdentityService $identityService,
    ) {}

    public function edit(Request $request): Response
    {
        $company = $this->resolveCompanyContext($request);
        $user = $this->identityService->ensureIdentity($request->user(), $company);

        return Inertia::render('Profile/Edit', [
            'profile' => $this->presentUser($user),
            'roleBadges' => $this->roleBadges($request),
            'currentCompany' => $company,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $company = $this->resolveCompanyContext($request);
        $validated = $request->validated();

        $payload = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        if ($request->hasFile('image')) {
            if ($user->image_path) {
                Storage::disk('public')->delete($user->image_path);
            }

            $payload['image_path'] = $request->file('image')->store('user-images', 'public');
        }

        $user->fill($payload);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->identityService->ensureIdentity($user, $company);

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        AuditEngine::log('AUTH', 'AUTH.ACCOUNT_DELETE_REQUESTED', [], [], $user);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function resolveCompanyContext(Request $request): ?Company
    {
        $shared = view()->shared('currentCompany');
        if ($shared instanceof Company) {
            return $shared->loadMissing('mainGroupCompany');
        }

        $subdomain = AppHost::extractSubdomain($request->getHost());

        if ($subdomain) {
            return Company::query()
                ->with('mainGroupCompany')
                ->whereRaw('LOWER(TRIM(subdomain)) = ?', [strtolower($subdomain)])
                ->where('is_active', true)
                ->first();
        }

        return $request->user()?->firstAccessibleCompany()?->loadMissing('mainGroupCompany');
    }

    private function presentUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'digital_id' => $user->digital_id,
            'image_url' => $this->identityService->imageUrl($user->image_path),
            'image_path' => $user->image_path,
            'created_at' => optional($user->created_at)?->toDateTimeString(),
        ];
    }

    private function roleBadges(Request $request): array
    {
        $rbac = app(\App\Http\Middleware\HandleInertiaRequests::class)->share($request)['auth']['rbac'] ?? [];

        return [
            'global' => collect($rbac['global_roles'] ?? [])->map(fn (string $role) => $this->formatRole($role))->values()->all(),
            'tenant' => collect($rbac['tenant_roles'] ?? [])->map(fn (string $role) => $this->formatRole($role))->values()->all(),
        ];
    }

    private function formatRole(string $role): string
    {
        return collect(explode('_', $role))
            ->map(fn (string $part) => ucfirst($part))
            ->join(' ');
    }
}
