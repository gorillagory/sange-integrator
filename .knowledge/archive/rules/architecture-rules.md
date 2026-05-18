# Architecture Rules

## Multi-Tenant Boundary

- `control` DB is the source of truth for identity, companies, memberships, and module enablement.
- `tenant` DB stores per-company operational data.
- Never assume tenant DB name statically; resolve by subdomain/company record.

## Domain Segmentation

- `sys.bayam.test`: control-plane routes and administration.
- `{subdomain}.bayam.test`: tenant-plane routes and company context.
- Middleware must enforce domain + membership + role constraints before controller logic.

## Connection Management

- When entering tenant context:
  1. set `database.connections.tenant.database`
  2. `DB::purge('tenant')`
  3. `DB::reconnect('tenant')`
  4. set default DB only where required by flow

- Permission team context must be set explicitly per request context.

## Migration Strategy

- Control schema evolves only via `database/migrations/control`.
- Tenant schema evolves via:
  - `database/migrations/tenant/shared`
  - `database/migrations/tenant/{industry}`
- Use `tenant:migrate` command to orchestrate across active tenants.

## Seeding Strategy

- Seed control DB first, then migrate/seed tenant DBs.
- Tenant data seeders must be target-scoped (subdomain/company aware).
- Optional seed data must fail-safe (warn and skip instead of breaking bootstrap).

## Operational Guardrails

- Cross-subdomain navigation during Inertia requests must use location-based full-page redirects.
- Keep setup/runbook docs updated whenever routing, migration paths, or tenant provisioning behavior changes.
