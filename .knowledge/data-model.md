# Data Model

This project separates data into two logical zones:

- Control DB (`control` connection, fixed database: `sange_control`)
- Tenant DB (`tenant` connection, database name set at runtime)

## Control DB Tables

Control migrations live under `database/migrations/control`.

1. `users`
- Auth identities

2. `companies` (`Schema::connection('control')`)
- Tenant directory with domain + database mapping

3. `company_user` (`Schema::connection('control')`)
- User-company membership and tenant-level role marker

4. Module and platform metadata
- `modules`
- `company_module` (module enablement per company)
- `service_schemas`
- `global_clients`
- `flights`
- `main_group_companies`
- `audit_logs`

5. Permission tables (control side)
- Spatie permission migration is present under control migrations

6. Laravel infrastructure tables
- `password_reset_tokens`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

## Tenant DB Tables

Tenant migrations are split by domain under `database/migrations/tenant`.

Shared tenant tables:
- `contracts` (+ company id extension)
- `document_templates`

Travel tenant tables:
- `bookings`
- `passengers`
- `booking_services`
- `invoice_template`
- booking upgrade migration (`upgrade_booking_module_table`)

## Relationship Summary

- `users` <-> `companies` is many-to-many via `company_user`.
- Each `companies` row points to a dedicated physical tenant DB via `db_name`.
- Tenant route access can be gated by enabled modules (`company_module` mapping).

## Important Assumption

Any command touching application data should run through Sail to avoid host PHP/version drift.
