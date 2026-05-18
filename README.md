# sange-integrator

Multi-tenant Laravel 13 + Inertia/Vue platform for system administration and tenant operations.

## Stack

- Backend: Laravel 13, PHP 8.3+, PostgreSQL
- Frontend: Inertia.js, Vue 3, Vite, Tailwind CSS v4
- Auth/RBAC: Laravel auth scaffolding + Spatie Permission with teams
- Runtime: Laravel Sail preferred for local development

## Architecture Summary

- Control domain: `sys.bayam.test`
- Tenant domains: `{subdomain}.bayam.test`
- Control database: `sange_control`
- Tenant databases: one database per company, selected dynamically from `companies.db_name`
- System users, RBAC, companies, and governance data live in control
- Contracts live in tenant/shared scope
- Operations and travel service capture live in tenant/travel scope

## Local Hostnames

Add these to local hosts/DNS and point them to `127.0.0.1`:

- `bayam.test`
- `sys.bayam.test`
- `bner.bayam.test`
- `bt.bayam.test`
- `enterprise.bayam.test`

## Expected `.env` Baseline

Use these values as the local default:

```dotenv
APP_URL=http://bayam.test:8080
SESSION_DOMAIN=.bayam.test
DB_CONNECTION=control
DB_DATABASE=sange_control
FORWARD_DB_PORT=5433
APP_PORT=8080
VITE_PORT=5174
```

Notes:

1. `DB_CONNECTION=control` is intentional. The app reads control data from the fixed `sange_control` database.
2. Tenant runtime switches to the correct tenant database at request time through middleware.
3. Prefer running framework commands through Sail to avoid host PHP version drift.

## Local Setup

```bash
cp .env.example .env
composer install
npm install
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --path=database/migrations/control --force
./vendor/bin/sail artisan db:seed --database=control --force
./vendor/bin/sail artisan tenant:migrate
./vendor/bin/sail artisan tenant:seed-document-template bt
./vendor/bin/sail npm run dev -- --host 0.0.0.0
```

## Local URLs

- App login: `http://bayam.test:8080/login`
- System workspace: `http://sys.bayam.test:8080`
- Tenant workspace example: `http://bt.bayam.test:8080`
- Vite dev server: `http://localhost:5174`
- Postgres forwarded port: `5433`

## Route Model

- System routes: company management, user management, blueprints, audit logs
- Tenant routes: bookings, clients, contracts, reports
- Tenant admin routes: schema builder and document builder

## Release Flow

Promotion path:

`feature branch -> pr -> staging -> production -> main`

## Reference Docs

- Canonical setup runbook: `.knowledge/setup-process.md`
- Route and access map: `.knowledge/routes-and-user-flows.md`
- Project tracker: `.knowledge/project-tracker.md`
- Workflow SOP: `.knowledge/workflow-sop.md`
