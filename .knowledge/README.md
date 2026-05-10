# Sange Integrator Knowledge Base

This folder is a working project memory for agent-assisted development.

Snapshot:
- Date: 2026-05-11
- Branch: `main`
- Commit: `6453312` (`feat(travel): add module gating and harden booking flow`)
- Git sync: `main` and `origin/main` are aligned at the same commit

Contents:
- `project-analysis.md`: current project state, assumptions, risks, and blockers
- `architecture.md`: application architecture and runtime flow
- `data-model.md`: database model across control and tenant boundaries
- `routes-and-user-flows.md`: route map and user journeys
- `project-tree.md`: curated repository tree for fast orientation
- `project-tracker.md`: actionable tracker for next milestones
- `mind-map.md`: compact conceptual map
- `git-flow.md`: branch roles and promotion rules
- `seeding-strategy.md`: deterministic seeding plan and runbook

Release flow:
- Feature branch -> `pr` -> `staging` -> `production` -> `main`
- Recovery backups available on remote:
  - `backup/remote-pre-rollback-5bba006`
  - `backup/recovered-pre-rewrite-6453312`

Update rule:
- Keep this folder updated when significant routes, schemas, infrastructure, or deployment assumptions change.
