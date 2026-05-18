# Project Analysis

## 1) Current Scope

This repository is a Laravel 13 + Inertia/Vue multi-tenant platform with:
- Domain-segmented access (`sys.bayam.test` and `{subdomain}.bayam.test`)
- System control panel for companies, blueprints, and users
- Tenant-side travel module (bookings, clients, contracts, reports)
- Tenant admin tools (schema builder + document template builder)
- Tenant RBAC and module gating (Spatie permission + custom middleware)

This knowledge snapshot was updated after restoring the fuller code line to commit `6453312`.

## 2) What Is Working Today (From Code Structure)

- Domain route split is active in `routes/web.php`.
- Tenant identification middleware resolves company by subdomain, enforces membership/super-admin checks, switches DB connection, and shares tenant context.
- Company module gating middleware (`company_module:*`) controls route access per tenant module enablement.
- Role middleware (`super_admin_or_tenant_role:*`) enforces role checks in tenant context.
- Docker/Sail Postgres connectivity is healthy (port map `5433:5432`, DB query successful).
- Restore branch/remote state is now stabilized at `6453312`.

## 3) What Is Partial / Not Yet Wired

- Documentation is still mostly default Laravel outside `.knowledge`.
- CI/branch protection policy is not yet enforced in repo settings.
- `.knowledge` content was previously based on a temporary reduced snapshot and needed this refresh.
- Local non-Sail PHP runtime mismatch can cause false negatives (`artisan` outside container).

## 4) Environment and Execution Notes

- `composer.json` requires `php: ^8.3`.
- Preferred execution path: Sail (`./vendor/bin/sail ...`).
- Host-level `php artisan` can fail platform checks if local PHP version differs from container runtime.
- `.env.example` includes duplicate `APP_PORT` lines (`8080` and `8000`), which can cause configuration confusion.

## 5) Immediate Risks

- Direct pushes without protections can rewrite critical history (already happened once).
- Large feature surface (travel + document builder + schemas + RBAC) lacks an explicit release checklist.
- Test reliability depends on consistently using Sail runtime and DB fixtures for domain-based flows.

## 6) Fast Win Priorities

1. Add branch protection for `main`, `production`, and `staging` (PR required, status checks, no force push except admins).
2. Define required checks for promotions (`pr` -> `staging` -> `production` -> `main`).
3. Clean and standardize `.env.example` (remove duplicate `APP_PORT`).
4. Add concise operational README sections for Sail boot, hostnames, and promotion flow.
