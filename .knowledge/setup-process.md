# Setup Process (Canonical)

## Scope

This runbook is the canonical local setup flow for this multi-tenant project.
Goal: bring app + control DB + tenant DBs into a consistent state.

## Environment Prerequisites

- OS: WSL2-backed Linux shell for Sail usage.
- Docker Desktop running.
- Laravel Sail dependencies already installed (`vendor` present).
- Node modules installed (`node_modules` present).

## Hostnames

Local DNS/hosts must resolve at least:

- `bayam.test`
- `sys.bayam.test`
- `bner.bayam.test`
- `bt.bayam.test`
- `enterprise.bayam.test`

All should point to `127.0.0.1`.

## Required .env Baseline

- `APP_URL=http://bayam.test:8080`
- `DB_CONNECTION=control`
- `DB_DATABASE=sange_control`
- `WWWUSER=<your-wsl-uid>`
- `WWWGROUP=<your-wsl-gid>`
- `APP_PORT=8080`
- `FORWARD_DB_PORT=5433`
- `VITE_PORT=5174`

Notes:

1. The control connection is hard-wired in `config/database.php` to database `sange_control`.
2. Tenant databases are selected dynamically from each company `db_name`.
3. `compose.yaml` publishes the app on `${APP_PORT}` and Vite on `${VITE_PORT}`.

## Bring Up Services

```bash
./vendor/bin/sail up -d
```

Check:

```bash
./vendor/bin/sail ps
```

## Database Bootstrap (Safe Order)

Run sequentially:

```bash
./vendor/bin/sail artisan migrate --path=database/migrations/control --force
./vendor/bin/sail artisan db:seed --database=control --force
./vendor/bin/sail artisan tenant:migrate
./vendor/bin/sail artisan tenant:seed-document-template bt
```

Why this order:

1. Build control schema first.
2. Seed control metadata/users/companies/modules.
3. Apply tenant schemas based on active companies from control DB.
4. Seed tenant-scoped template data.

Optional visibility check:

```bash
./vendor/bin/sail artisan tenant:migrate --list
```

## Frontend Dev Server

```bash
./vendor/bin/sail npm run dev -- --host 0.0.0.0
```

If needed, ensure `public/hot` points to active Vite port (example):

```text
http://localhost:5174
```

## App Access

- Main login: `http://bayam.test:8080/login`
- System area: `http://sys.bayam.test:8080`
- Tenant area example: `http://bt.bayam.test:8080`

## Troubleshooting

- PHP version mismatch on host: run commands through Sail, not host PHP.
- Vite EACCES in `node_modules/.vite-temp`: align `WWWUSER`/`WWWGROUP` to host UID/GID and recreate Sail.
- Inertia cross-subdomain XHR redirects: must use Inertia location responses for cross-origin route jumps.
- Control DB boot issues: make sure `.env` uses `DB_CONNECTION=control` and `DB_DATABASE=sange_control`.
