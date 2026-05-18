# Sange Integrator Knowledge Base

This folder is the active project memory for the application and its execution history.

## Read This First

For active work, start in this order:

1. `setup-process.md`
2. `development-rules.md`
3. `architecture.md`
4. `data-model.md`
5. `workflow-sop.md`

## Active Docs

These files are the current source of truth:

- `architecture.md`
  Current product direction and runtime architecture.
- `architecture-assessment.md`
  Architect-facing assessment of the current runtime, strengths, risks, and next decisions.
- `target-state-architecture.md`
  Final intended platform architecture and source-of-truth decisions.
- `data-model.md`
  Control/tenant data boundaries and dynamic capture model.
- `transition-roadmap.md`
  Phased path from the current transition state to the target architecture.
- `booking-compatibility-deprecation-plan.md`
  Explicit inventory, stages, and removal conditions for booking-era compatibility.
- `handler-contract-specification.md`
  First-class handler contract for runtime, documents, and extraction.
- `service-handling-flow.md`
  End-to-end flow of how service vectors move through capture, documents, and extraction.
- `service-handling-mind-map.md`
  Concept map of the dynamic service handling model.
- `setup-process.md`
  Canonical local environment and bootstrap flow.
- `development-rules.md`
  Coding, schema, workflow, and runtime safety rules.
- `seeding-strategy.md`
  Deterministic bootstrap and seeder order.
- `audit-trail-logging.md`
  Current audit implementation and hardening notes.
- `git-flow.md`
  Promotion and branch governance flow.
- `uiux-rules.md`
  UI standards for operational product surfaces.
- `workflow-sop.md`
  Mandatory execution workflow.
- `workflow-history.md`
  High-level index of completed history artifacts, ordered from oldest (`01`) to latest.

## History And Archive

To preserve past thinking without cluttering the active knowledge set:

- `work/completed/`
  Execution ledger and detailed implementation history, stored as `NN-History-<taskname>.md`.
- `subtask/`
  Historical drafts, reviews, and focused design investigations.
- `archive/`
  Older reference snapshots and superseded root docs kept for traceability.

## Current Product Direction

The application should be understood as a **dynamic company operations platform**, not a fixed “booking app” or any one domain-specific system.

Current architectural direction:

1. Capture operational data dynamically through vectors/schemas.
2. Handle operational documents from the same vector-driven payloads.
3. Capture finance-adjacent values at operations time:
   - quantity
   - supplier pricing
   - markup
   - discount
   - tax
   - client charge
4. Treat domain-specific reporting and analytics as a later projection/processing concern, not the main runtime storage model.

## Quick Notes

- Control DB is fixed: `sange_control`
- Tenant DB is dynamic per company
- Service vectors are more important than hardcoded child models
- Historical docs are preserved; active docs should stay small and current
