# RBAC Sidebar Enforcement - 015

Status: in_progress

## Checklist

- [x] Verify current sidebar RBAC behavior on `SystemLayout.vue` and `TenantLayout.vue`.
- [x] Verify RBAC context source in `HandleInertiaRequests.php`.
- [x] Align system route-level authorization with sidebar policy for `system_admin` vs `super_admin`.
- [x] Run build/syntax validation after RBAC changes.
- [x] Record outcome and move checklist to `completed`.

## Verification Notes

- Sidebar visibility currently uses `auth.rbac` flags.
- Route policy now matches sidebar: `companies` and `users` endpoints require `role:super_admin`.
- `php -l routes/web.php` passed.
- Full runtime verification is pending in Linux Sail environment due local runner mismatch (`PHP 8.2` on host, no host `vite` binary, and `./vendor/bin/sail` does not emit output in this PowerShell session).

Status: completed
