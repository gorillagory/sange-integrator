# Development Rules

Last updated: 2026-05-18

## Core Workflow

1. Use Sail for PHP, Artisan, database, and framework verification work.
2. Keep control-plane and tenant-plane changes explicit.
3. Run migrations and seeders sequentially, not in parallel.
4. Keep checklist-based execution under `.knowledge/work/working` -> `.knowledge/work/completed`.

## Dynamic Runtime Rules

1. Prefer vector-driven payload capture over hardcoded domain-specific fields.
2. Do not introduce new runtime child tables just because one service/workflow needs extra fields.
3. Treat service-instance payloads as the primary operational truth unless a normalized model is clearly necessary.
4. Keep schema identity explicit and version-aware.
5. Preserve replay/document safety through snapshots where needed.

## Modeling Rules

1. Parent workflow records may hold context and totals.
2. Service-instance records should hold dynamic governed payloads and commercial values.
3. Do not let analytics/reporting requirements prematurely hardcode the runtime model.
4. Build projections later when business-specific panels truly need normalized structures.

## Database Safety

- Never run destructive commands on all DBs without explicit intent.
- Control migrations belong under `database/migrations/control`.
- Tenant migrations belong under `database/migrations/tenant/{shared|industry}`.
- Tenant runtime connection must be set via config + `DB::purge('tenant')` + `DB::reconnect('tenant')`.

## Routing and Auth

- System routes are domain-scoped to `sys.bayam.test`.
- Tenant routes are domain-scoped to `{subdomain}.bayam.test`.
- Cross-subdomain redirects from Inertia requests must return Inertia location responses, not plain `302`.

## Document and Finance Rules

1. Operational capture must support finance-adjacent values at source:
   - qty
   - supplier pricing
   - discount
   - tax
   - markup
   - client pricing
2. Documents should consume structured runtime payloads, not ad hoc flat mappings.
3. New document work should preserve nested payload-path conventions.

## Code Change Rules

- Keep edits scoped and minimal.
- Preserve existing naming and file organization patterns unless the change is an intentional simplification.
- Add comments only where behavior is non-obvious.
- Do not mix unrelated refactors with feature/bugfix work.

## Quality Gates

- Syntax check changed PHP files.
- Run relevant frontend/static verification for changed UI flows.
- Re-run relevant tests in Sail whenever DB, auth, tenant middleware, or payload contracts are modified.

## Workflow Enforcement

1. All implementation work must use the checklist flow under `.knowledge/work/working` -> `.knowledge/work/completed`.
2. Active checklist naming is mandatory in `working`: `(taskname)-(tasknumber)-checklist.md`.
3. Completed history naming is mandatory in `completed`: `NN-History-<taskname>.md`.
4. No phase is considered done until checklist status is `completed`, the file is moved to `completed`, and the history filename is renumbered into chronological order.
