# Bayam Travel Role Users Seeder 012 Checklist

Status: `completed`  
Owner: Codex + User  
Created: 2026-05-13

## Tasks

- [x] Create dedicated seeder for Bayam Travel role users (one user per role).
- [x] Use a shared password for all seeded role users.
- [x] Ensure idempotent behavior (`updateOrCreate` / upsert style).
- [x] Wire seeder into `DatabaseSeeder`.
- [x] Execute seeder in Sail.
- [x] Verify control DB row counts after seeding.

## Verification Notes

1. Seeder executed:
   - `./vendor/bin/sail artisan db:seed --database=control --class=Database\\Seeders\\BayamTravelRoleUsersSeeder --force`
2. Control DB counts now show expected growth:
   - `users`: 7 rows (genesis super admin + 6 Bayam Travel role users)
   - `model_has_roles`: 7 rows
   - `company_user`: 6 rows
