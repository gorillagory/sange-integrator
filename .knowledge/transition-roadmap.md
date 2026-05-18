# Transition Roadmap

Last updated: 2026-05-15

## Purpose

This document defines the phased path from the current transition architecture to the target-state architecture.

It is intended to guide implementation sequencing.

## Current State Summary

Current strengths:

- canonical `operations` routes exist
- canonical `Operation` and `ServiceInstance` models exist
- dynamic service payload capture is active
- passenger writes are removed from active runtime
- canonical document roots exist
- handler-aware extraction exists
- projection materialization exists
- operation review pages read from `service_instances`
- booking compatibility routes, wrappers, and render aliases are removed from active runtime

Current transition debt:

- physical compatibility storage still exists in legacy columns/tables (`invoice_no`, `cart_payload`, `passenger_details`, `passengers`)
- module key naming still uses `travel.booking`
- document numbering remains invoice-shaped even though runtime wording is generalized
- only the reference handler (`travel.services`) is implemented so far

## Execution Status

The roadmap phases have now been implemented at the application layer.

Remaining follow-through is operational rather than architectural:

- run migrations and smoke checks in a PHP `>= 8.4` runtime
- migrate any tenant-authored templates still using old bindings, if they exist outside current source control
- decide when physical compatibility columns/tables should be archived or dropped

## Roadmap Principles

1. Protect captured tenant data first.
2. Remove duplicate truth before deepening downstream systems.
3. Finish compatibility transition before expanding template contract again.
4. Formalize handler contracts before building handler-specific analytics.

## Phase 1. Runtime Truth Consolidation

### Outcome

Runtime line-level truth is unambiguous.

### Work

- classify `operations.cart_payload` as derived cache or remove it
- make `service_instances` the only authoritative line-level runtime record
- update read paths to hydrate from `service_instances`
- ensure operation review pages no longer depend on duplicate serialized payload structures

### Acceptance Criteria

- all line-level UI views render from `service_instances`
- create/update flows do not require duplicate line serialization
- document generation does not depend on `cart_payload`
- architecture docs still align with runtime behavior

### Risks

- UI regressions if current pages still expect `cart_payload`
- hidden dependencies in report or document preview layers

## Phase 2. Compatibility Freeze

### Outcome

Legacy booking compatibility remains available but is clearly frozen.

### Work

- mark booking compatibility surfaces as legacy-only
- stop adding any new logic to booking aliases
- update internal docs to enforce canonical `operations` contract for all new work

### Acceptance Criteria

- all new runtime code uses `operations`/`service_instances`
- all new docs use canonical nouns only
- compatibility is present only as adapter behavior

### Risks

- teams may continue building against old alias names if freeze is not explicit

## Phase 3. Handler Contract Formalization

### Outcome

Handlers become explicit platform contracts instead of hardcoded conventions.

### Work

- define handler metadata and lifecycle
- formalize handler registration expectations
- formalize handler-aware extraction interfaces
- define handler document policy and schema policy

### Acceptance Criteria

- a written handler contract exists and is implementation-guiding
- new handlers can be reasoned about without re-deriving platform assumptions
- extraction and document layers can identify handler-specific extension points

### Risks

- over-engineering if handler abstraction is too complex too early
- under-specification if handler metadata is too weak

## Phase 4. Document Architecture Consolidation

### Outcome

Document authoring is fully canonical.

### Work

- move template guidance fully to canonical roots
- migrate active templates off `booking.*`
- reduce or remove transitional render aliases
- begin separating operation lifecycle from invoice lifecycle

### Acceptance Criteria

- active templates render using canonical payload roots only
- `booking.*` aliases are no longer required for current templates
- document architecture can support more than invoice-specific flows

### Risks

- template migration breakage
- author confusion during contract transition

## Phase 5. Extraction And Projection Expansion

### Outcome

Analytics becomes handler-aware and projection-ready.

### Work

- add handler-aware extractors
- define normalized projection persistence strategy
- create first materialized reporting model(s)
- define classification/enrichment boundaries for future AI panels

### Acceptance Criteria

- extraction supports handler-aware branching
- at least one materialized downstream reporting path exists
- runtime and analytics responsibilities stay separate

### Risks

- reporting demands may try to pull logic back into runtime tables
- inconsistent schema governance can weaken projections

## Phase 6. Compatibility Removal

### Outcome

Booking compatibility is removed or isolated to a legacy adapter boundary.

### Work

- remove booking route/controller/model compatibility surfaces
- remove `booking.*` document aliases
- remove or archive legacy passenger-oriented runtime remnants that are no longer required
- rename tactical fields if final document model requires it

### Acceptance Criteria

- canonical runtime no longer depends on booking compatibility
- new templates and runtime flows do not reference booking aliases
- migration notes and history remain preserved in knowledge docs

### Risks

- older tenant templates or links may still rely on deprecated paths
- cleanup may be delayed if no migration ownership exists

## What Can Happen Immediately

- runtime truth consolidation planning
- compatibility freeze
- handler specification
- canonical documentation updates

## What Should Wait Until Stability Improves

- destructive removal of legacy columns/tables
- deep document lifecycle restructuring
- broad analytics persistence rollout

## Rollback Thinking

For any phase that changes runtime behavior:

- keep compatibility adapters until consumers are migrated
- prefer additive migration steps before destructive ones
- validate document outputs before removing aliases
- preserve historical docs/checklists as rollback reference material

## Recommended Execution Order

1. Phase 1
2. Phase 2
3. Phase 3
4. Phase 4
5. Phase 5
6. Phase 6

This order minimizes churn and keeps the highest-risk removals last.
