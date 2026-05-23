<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserIdentityService
{
    public function ensureIdentity(User $user, ?Company $company = null): User
    {
        $company ??= $user->firstAccessibleCompany();

        $payload = [];

        if (! filled($user->username)) {
            $payload['username'] = $this->generateUniqueUsername($user->name, $user->email, $user->id);
        }

        if (! filled($user->digital_id)) {
            $payload['digital_id'] = $this->generateUniqueDigitalId($company, $user->id);
        }

        if ($payload !== []) {
            $user->forceFill($payload)->save();
            $user->refresh();
        }

        return $user;
    }

    public function generateUniqueUsername(string $name, string $email, ?int $ignoreUserId = null): string
    {
        $base = Str::of($name)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.')
            ->value();

        if ($base === '') {
            $base = Str::before((string) $email, '@');
        }

        $base = trim(Str::lower($base), '.');
        $base = $base !== '' ? $base : 'user';

        $candidate = $base;
        $suffix = 1;

        while ($this->usernameExists($candidate, $ignoreUserId)) {
            $candidate = $base.'.'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    public function generateUniqueDigitalId(?Company $company = null, ?int $ignoreUserId = null): string
    {
        $mainGroupCode = $this->initials($company?->mainGroupCompany?->name ?: config('app.name', 'Sange Central'), 2);
        $companyCode = $this->initials($company?->name ?: 'System', 3);
        $yearCode = now()->format('y');

        do {
            $candidate = sprintf('%s%s%s-%05d', $mainGroupCode, $companyCode, $yearCode, random_int(0, 99999));
        } while ($this->digitalIdExists($candidate, $ignoreUserId));

        return $candidate;
    }

    public function imageUrl(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }

        if (Str::startsWith($path, '/storage/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function usernameExists(string $username, ?int $ignoreUserId = null): bool
    {
        return User::query()
            ->when($ignoreUserId, fn ($query) => $query->where('id', '<>', $ignoreUserId))
            ->where('username', $username)
            ->exists();
    }

    private function digitalIdExists(string $digitalId, ?int $ignoreUserId = null): bool
    {
        return User::query()
            ->when($ignoreUserId, fn ($query) => $query->where('id', '<>', $ignoreUserId))
            ->where('digital_id', $digitalId)
            ->exists();
    }

    private function initials(string $value, int $maxLength = 2): string
    {
        $parts = collect(preg_split('/[^A-Za-z0-9]+/', Str::upper(Str::ascii($value))) ?: [])
            ->filter()
            ->values();

        if ($parts->isEmpty()) {
            return str_pad('ID', $maxLength, 'X');
        }

        $initials = $parts
            ->map(fn (string $part) => Str::substr($part, 0, 1))
            ->join('');

        if (strlen($initials) < $maxLength) {
            $initials .= Str::substr($parts->implode(''), 1, $maxLength - strlen($initials));
        }

        return Str::upper(Str::substr($initials, 0, $maxLength));
    }
}
