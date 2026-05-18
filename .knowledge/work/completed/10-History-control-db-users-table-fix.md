# Control DB Users Table Fix 010 Checklist

Status: `completed`  
Owner: Codex + User  
Created: 2026-05-13

## Tasks

- [x] Diagnose `relation "users" does not exist` on control connection.
- [x] Validate control migration state and table inventory.
- [x] Run control migrations in correct path (`database/migrations/control`).
- [x] Run control seeders after schema creation.
- [x] Run tenant migrations and tenant template seed to restore full baseline.
- [x] Clear caches and verify `sys.bayam.test:8000/login` is reachable.

## Verification Notes

1. Control migration completed:
   - `./vendor/bin/sail artisan migrate --database=control --path=database/migrations/control --force`
2. Control seeding completed:
   - `./vendor/bin/sail artisan db:seed --database=control --force`
3. Tenant migration completed:
   - `./vendor/bin/sail artisan tenant:migrate`
4. Tenant document template seed completed:
   - `./vendor/bin/sail artisan tenant:seed-document-template bt`
5. Cache clear completed:
   - `./vendor/bin/sail artisan optimize:clear`
6. Runtime check:
   - `http://sys.bayam.test:8000/login` returned HTTP `200`.
