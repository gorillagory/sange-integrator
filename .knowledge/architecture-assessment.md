# Architecture Assessment

Last updated: 2026-05-15

## Purpose

This document is an architect-facing assessment of the current runtime after the operations generalization pass.

It answers:

1. what the architecture is now
2. what is structurally strong
3. what is still transitional
4. what decisions should be made next

## Executive Summary

The application is now correctly moving toward a **dynamic operations platform** rather than a booking-specific system.

The strongest part of the current architecture is the runtime model:

- `operations` is now the lightweight workflow/commercial envelope
- `service_instances` is now the primary operational record
- dynamic vector payloads are the operational truth
- finance values are captured at the same point as operational data
- document generation now consumes canonical operation/service payload roots
- analytics is starting as an extraction layer rather than a reporting-first table design

This is the right direction.

However, the implementation is still in a **transition architecture**, not yet a fully consolidated platform architecture.

The main reasons are:

- booking compatibility classes/routes still exist
- some legacy naming remains in storage and payload compatibility
- `cart_payload` duplicates data already stored in `service_instances`
- `handler_key` is hardcoded for the current travel flow
- extraction is generic scaffolding only, not yet handler-aware

Architecturally, this is a strong foundation with clear next consolidation steps.

## Current Runtime Shape

### 1. Control Plane

The control plane remains correctly separated.

Responsibilities:

- tenant registry
- module enablement
- user identity and memberships
- schema/vector governance through `service_schemas`
- global/shared metadata

This is correct and should remain stable.

### 2. Tenant Plane

The tenant plane now carries the operational runtime.

Primary records:

- `operations`
- `service_instances`
- `contracts`
- `document_templates`

This is also directionally correct.

### 3. Runtime Capture Path

The active runtime path is:

1. tenant route resolves company context
2. operations UI loads available schemas/vectors
3. user selects client + contract
4. user adds one or more service vectors
5. vector-defined fields populate `service_details`
6. commercial values are entered alongside the payload
7. runtime validates payloads against the selected schema
8. parent `operation` record is created
9. child `service_instances` are created
10. payload snapshots are stored for replay and downstream use

This is the right operational capture model.

### 4. Document Handling Path

The document pipeline now builds from canonical roots:

- `operation`
- `services`
- `service_instances`
- `finance`
- `client`
- `company`

This is a meaningful improvement because document rendering now aligns with the generalized model instead of depending on passengers or booking-only assumptions.

There is still a compatibility alias:

- `booking.*`

That is acceptable as a transition layer, but it should not remain the primary authoring contract for too long.

### 5. Analytics / Extraction Path

The platform now has the beginning of a proper projection layer.

Current state:

- generic extractor contract exists
- manager/registry pattern exists
- normalized extraction row structure exists
- dry-run CLI entrypoint exists

This is good architecture.

But it is only scaffolding today:

- no handler-specific extractors yet
- no persistence/materialization yet
- no downstream panels yet

That is fine for this phase, as long as this remains explicit.

## What Is Structurally Strong

### Vector-first operational truth

This is the biggest architectural win.

The runtime no longer needs dedicated child tables like passengers in order to function.
That keeps the platform flexible across industries and workflows.

### Clean parent/child operational split

The separation between:

- operation as envelope
- service instance as truth-bearing unit

is strong and easy to extend.

### Finance captured at source

The runtime correctly acknowledges that operations users produce financial reality at capture time:

- qty
- supplier cost
- tax
- client price
- totals

This is important and should remain a first-class design principle.

### Downstream analytics model

The architecture now correctly avoids over-normalizing runtime storage just to satisfy future reports.
That is a strong long-term decision.

## What Is Transitional Or Weak

### 1. Dual truth between `service_instances` and `cart_payload`

Current issue:

- `service_instances` stores the authoritative per-line operational record
- `operations.cart_payload` stores a second serialized view of the same service lines

Risk:

- data drift
- unclear source of truth
- more maintenance logic over time

Architect recommendation:

- make `service_instances` the only authoritative line-level truth
- keep `cart_payload` only if it becomes an explicitly derived cache
- otherwise phase it out

### 2. Legacy compatibility still visible in runtime structure

Current examples:

- booking compatibility routes
- booking compatibility classes
- `booking.*` document alias
- `invoice_no` naming instead of a more generic document identifier
- `passenger_details` and legacy passenger structures still physically present

This is acceptable during migration, but it is not a good final architecture state.

Architect recommendation:

- define a formal transition window
- document the deprecation boundary
- remove or isolate these compatibility paths in a later cleanup phase

### 3. Handler model is not yet fully generalized

Current state:

- `handler_key` exists
- current value is effectively fixed to `travel.services`

This means the architecture is prepared for multiple handlers, but the platform is not yet operating as a handler registry.

Architect recommendation:

- define handler contracts explicitly
- decide whether handlers are:
  - configuration only
  - code + configuration
  - module-scoped runtime packages

### 4. Extraction layer is not yet business-aware

Current state:

- generic normalized extraction works
- but all extraction semantics are still flat/generic

This is enough for scaffolding, not enough for intelligence panels.

Architect recommendation:

- define handler-aware extractor interfaces
- define enrichment stages:
  - raw extraction
  - normalization
  - classification
  - materialization

### 5. Document state is still invoice-centric

Current runtime currently locks and generates mainly around invoice behavior.

That is acceptable for travel billing, but the platform goal is broader:

- operational documents
- finance documents
- supporting documents
- future multi-document workflows

Architect recommendation:

- separate operational lifecycle from invoice lifecycle
- model document outputs as one capability of an operation, not the only endpoint

## Architectural Risks To Watch

### Schema governance risk

If vectors become flexible but not governed, the downstream analytics layer will become noisy and hard to trust.

Mitigation:

- strict schema versioning
- stable field keys
- clear rules for governed vs ad hoc fields
- disciplined use of `service_details_extra`

### Compatibility drift risk

If legacy booking aliases stay too long, teams will keep building against them.

Mitigation:

- mark canonical payloads clearly in docs
- set a removal phase for old aliases

### Queryability risk

A fully dynamic payload model is strong for capture, but weak for ad hoc querying if projection pipelines are delayed too long.

Mitigation:

- prioritize extraction/materialization soon after capture architecture stabilizes

## Recommended Next Architectural Decisions

### Immediate

1. Declare the single line-level source of truth.
   - Recommendation: `service_instances`

2. Define the compatibility boundary.
   - Recommendation: keep aliases only for transition, not for new development

3. Formalize handler identity.
   - Recommendation: move from hardcoded `travel.services` toward a documented handler contract

### Near-term

1. Introduce handler-aware extractor registration.
2. Define a materialized analytics/projection strategy.
3. Separate document lifecycle from operation lifecycle.
4. Decide whether `invoice_no` should remain a tactical field or evolve into a broader document-output model.

### Mid-term

1. Build a processing panel architecture for:
   - analytics
   - enrichment
   - summarization
   - business-specific model feeding
2. Remove remaining booking/passenger runtime dependency surfaces.
3. Convert compatibility wrappers into explicit legacy adapters or retire them.

## Architectural Verdict

The architecture is now **directionally correct and worth continuing**.

It has crossed the important conceptual boundary:

- from domain-first booking storage
- to dynamic operations capture with vector-defined service payloads

That is the right foundation for:

- multi-industry operations
- document handling
- finance-aware runtime capture
- future AI-assisted analytics panels

The next architect-level priority is not rethinking the model again.
The next priority is **consolidation**:

1. remove duplicate truth
2. formalize handler contracts
3. strengthen extraction/materialization
4. finish the transition away from legacy booking compatibility surfaces
