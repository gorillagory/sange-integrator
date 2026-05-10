# Project Tracker

## Snapshot

- Date: 2026-05-11
- Branch: `main`
- Commit: `6453312`
- Remote sync: aligned (`origin/main` == `main`)

## Milestone Status

1. Foundation: complete
- Laravel 13 app bootstrapped
- Inertia/Vue operational frontend present
- Domain-based route split in place
- Control + tenant DB switching/gating middleware present

2. Multi-tenant runtime isolation: active
- Company directory and membership schema exists
- Tenant migration command exists
- Tenant middleware resolves tenant and enforces access checks

3. RBAC layer: partial to advanced
- Spatie permission integration exists
- Team-aware checks are used in middleware
- Seeder/workflow hardening still needed for predictable role bootstrap

4. Module surfaces: active
- Travel booking operations
- Client/contract/report workflows
- Admin schema and document builder surfaces

5. Git governance: established
- Remote rollback executed from `5bba006` to `6453312`
- Remote backups created:
  - `backup/remote-pre-rollback-5bba006`
  - `backup/recovered-pre-rewrite-6453312`
- Promotion branches created:
  - `pr`
  - `staging`
  - `production`

## Known Technical Debt

- Branch protection rules are not enforced yet in GitHub settings.
- Promotion checklist is not codified as required checks.
- `SubdomainRouteTest` requires validation against current route expectations.
- `.env.example` contains duplicate `APP_PORT`.
- Root `README.md` still does not describe project architecture/flow.

## Next Actions (Ordered)

1. Enable branch protections for `main`, `production`, and `staging`.
2. Define required CI checks before merge/promotion.
3. Normalize environment docs and config defaults (ports, DB connection expectations).
4. Run and align full test suite in Sail against current domain/middleware flow.
5. Document operational release runbook in root README.
