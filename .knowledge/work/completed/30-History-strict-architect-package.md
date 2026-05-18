# Strict Architect Package 030 Checklist

Status: `completed`
Owner: `Codex`
Created: `2026-05-15`

## Objective

Convert the current architecture assessment into a stricter architect package that is precise enough to guide platform consolidation and phased execution.

## Package Deliverables

1. `target-state-architecture.md`
   - final intended runtime architecture
   - canonical concepts and boundaries
   - source-of-truth decisions
   - operational/document/analytics layer separation

2. `transition-roadmap.md`
   - phased migration from current transition state to target state
   - sequencing, dependencies, and acceptance criteria
   - risk controls and rollback thinking

3. `booking-compatibility-deprecation-plan.md`
   - compatibility inventory
   - deprecation stages
   - removal criteria
   - communication and template migration notes

4. `handler-contract-specification.md`
   - handler identity model
   - handler lifecycle
   - runtime interfaces
   - schema, extraction, document, and projection contract rules

## Planned Output Location

- `.knowledge/target-state-architecture.md`
- `.knowledge/transition-roadmap.md`
- `.knowledge/booking-compatibility-deprecation-plan.md`
- `.knowledge/handler-contract-specification.md`

## Execution Plan

### Phase 1. Baseline Reassessment

- [x] Re-read the active architecture docs and current generalized runtime implementation.
- [x] Reconcile current-state facts with the latest code:
  - `operations`
  - `service_instances`
  - document payload roots
  - extraction scaffolding
  - compatibility layers
- [x] Identify all transition-state inconsistencies that must be addressed explicitly in the package.

### Phase 2. Target-State Architecture

- [x] Define final canonical runtime nouns and boundaries:
  - operation
  - service instance
  - handler
  - schema/vector
  - document output
  - projection/extraction
- [x] Decide and document the single source of truth for line-level operational data.
- [x] Define the role of `operation` versus `service_instance` in the final platform.
- [x] Define document architecture in the target state:
  - canonical payload roots
  - template authoring contract
  - compatibility boundary
- [x] Define target-state analytics/projection architecture at a high level.

### Phase 3. Transition Roadmap

- [x] Break the transition into explicit phases with outcomes and dependencies.
- [x] Define what can happen immediately versus what must wait for runtime stabilization.
- [x] Add acceptance criteria for each phase.
- [x] Add risk notes for each phase:
  - data drift
  - legacy template breakage
  - migration safety
  - reporting gaps

### Phase 4. Booking Compatibility Deprecation Plan

- [x] Inventory all booking compatibility surfaces:
  - routes
  - controller aliases
  - model aliases
  - document payload aliases
  - UI compatibility wrappers
  - legacy columns/tables
- [x] Group them by deprecation stage:
  - keep temporarily
  - migrate consumers
  - freeze
  - remove
- [x] Define explicit removal conditions for each compatibility surface.
- [x] Define recommended timing for compatibility removal.

### Phase 5. Handler Contract Specification

- [x] Define the handler concept as a first-class platform contract.
- [x] Specify required handler metadata:
  - `handler_key`
  - module/industry scope
  - runtime capabilities
  - schema policy
  - document policy
  - extraction policy
- [x] Define handler responsibilities in capture, validation, document generation, and extraction.
- [x] Define extension points for handler-aware extractors and future AI processing panels.
- [x] Clarify what belongs in configuration versus code.

### Phase 6. Package Consolidation

- [x] Cross-check all four docs for terminology consistency.
- [x] Ensure the package distinguishes clearly between:
  - current state
  - transition state
  - target state
- [x] Update `.knowledge/README.md` to index the new architect package docs.
- [x] Update `.knowledge/subtask/solution-draft.md` and `.knowledge/workflow-history.md` after the package is completed.

## Verification Standard For The Package

- [x] Each document must contain concrete decisions, not only observations.
- [x] Each document must use the same canonical terms throughout.
- [x] Deprecation plan must include explicit removal conditions.
- [x] Handler spec must be implementation-guiding, not conceptual only.
- [x] Roadmap phases must be sequenced and testable.

## Notes

- Architect package docs are now active and indexed in `.knowledge/README.md`.
- This checklist should be moved to `completed` after closeout.
