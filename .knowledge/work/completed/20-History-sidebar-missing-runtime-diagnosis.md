# Sidebar Missing Runtime Diagnosis - 020

Status: in_progress

## Checklist

- [x] Verify control DB role assignments for super admin and role mappings.
- [x] Confirm issue is not permission seeding gap (`model_has_roles` contains global super admin assignment).
- [x] Clear Laravel and permission caches in runtime container.
- [x] Rebuild frontend assets in runtime container.
- [x] Remove stale Vite hot marker (`public/hot`) to force built assets.
- [x] Move checklist to `completed`.

## Verification Notes

- `model_has_roles` includes `App\Models\User` with `role_id=1` and `company_id=0` for super admin user.
- `npm run build` completed successfully in `sange-integrator-laravel.test-1`.

Status: completed
