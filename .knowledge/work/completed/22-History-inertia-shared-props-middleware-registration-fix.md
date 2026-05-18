# Inertia Shared Props Middleware Registration Fix - 022

Status: in_progress

## Checklist

- [x] Diagnose missing sidebar/auth payload against runtime layout output.
- [x] Confirm `HandleInertiaRequests` was not registered in web middleware pipeline.
- [x] Register `HandleInertiaRequests` (and preload link middleware) in `bootstrap/app.php`.
- [x] Clear framework caches in container.
- [x] Move checklist to `completed`.

## Verification Notes

- Missing shared props (`auth.user`, `auth.rbac`, `brand`) matched unregistered middleware behavior.
- `php -l bootstrap/app.php` passed.

Status: completed
