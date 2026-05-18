# RBAC Super Admin Sidebar Regression Fix - 018

Status: in_progress

## Checklist

- [x] Analyze regression path for super admin sidebar visibility.
- [x] Harden global role lookup in middleware against `company_id` scope drift (`0` vs `NULL`).
- [x] Remove hardcoded model type dependency in RBAC role lookup.
- [x] Align `User::hasGlobalRole()` with tolerant global scope matching.
- [x] Run syntax validation for modified PHP files.
- [x] Move checklist to `completed`.

## Verification Notes

- Expected: super admin receives `is_super_admin=true` and system sidebar items reappear.
- `php -l app/Http/Middleware/HandleInertiaRequests.php` passed.
- `php -l app/Models/User.php` passed.

Status: completed
