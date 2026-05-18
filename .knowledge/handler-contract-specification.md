# Handler Contract Specification

Last updated: 2026-05-15

## Purpose

This document specifies the handler contract for the dynamic operations platform.

A handler is the platform boundary that gives operational meaning to generic runtime capture without forcing the entire system into fixed domain models.

## Definition

A handler is a named runtime contract that defines:

- which schema family a service instance belongs to
- how runtime validation behaves
- what document conventions apply
- how extraction/projection should interpret captured payloads

Example current handler:

- `travel.services`

## Required Handler Metadata

Every handler must define:

- `handler_key`
- human-readable name
- industry or module scope
- status
- supported runtime capabilities
- schema governance policy
- document policy
- extraction policy

## Canonical Handler Identity

### `handler_key`

Rules:

- globally unique within the platform
- stable over time
- machine-readable
- not derived from display labels

Recommended format:

- `<industry>.<family>`

Examples:

- `travel.services`
- future equivalents for other industries

## Handler Lifecycle

### Draft

- contract being designed
- not yet available for runtime capture

### Active

- available for new operations
- allowed in runtime capture and extraction

### Frozen

- no new schema family expansion without explicit approval
- existing operations remain readable/renderable

### Deprecated

- not available for new operations
- still readable for historical compatibility

## Runtime Responsibilities

### Capture responsibility

A handler defines:

- which schema family is available
- how service vectors should be grouped
- whether any additional runtime conventions apply

The generic platform still owns:

- tenancy
- persistence
- security
- operation/service-instance lifecycle

### Validation responsibility

The handler must support:

- schema-driven validation
- governed field rules
- documented allowance for ad hoc extension fields

The platform owns the validation engine.
The handler owns the semantics of what should be validated.

### Document responsibility

A handler defines:

- canonical template conventions
- any handler-specific document assumptions
- which document types are meaningful for that handler

The platform owns:

- rendering engine
- payload assembly framework
- preview/download mechanics

### Extraction responsibility

A handler defines:

- how service payloads should be normalized
- what dimensions and metrics matter
- what enrichment hooks exist

The platform owns:

- extraction orchestration
- registry/manager lifecycle
- normalized output envelope

## Required Contract Areas

### 1. Schema Policy

A handler must define:

- which schema records belong to it
- versioning expectations
- governed field discipline
- `service_details` versus `service_details_extra` usage rules

### 2. Document Policy

A handler must define:

- canonical document payload expectations
- expected line-item conventions
- any handler-specific field exposure guidance

### 3. Extraction Policy

A handler must define:

- mandatory normalized dimensions
- mandatory normalized metrics
- optional enrichments
- downstream projection expectations

## Handler-Aware Extension Points

The platform must support handler-aware branching at these points:

- schema filtering/loading
- payload validation augmentation
- document payload enrichment
- extraction strategy selection
- future AI processing pipelines

## Configuration Vs Code

### Configuration should hold

- handler metadata
- schema associations
- document availability flags
- projection feature flags

### Code should hold

- validation engine behavior
- extraction implementations
- document payload assemblers
- any handler-specific orchestration logic that cannot be expressed safely as pure configuration

## Minimum Handler Contract For Activation

Before a handler is considered active, it must have:

1. a stable `handler_key`
2. defined schema ownership rules
3. runtime validation policy
4. document policy
5. extraction policy
6. lifecycle status

## Current Implementation Gap

Current runtime supports only the first slice of this contract:

- `handler_key` exists
- extraction registry pattern exists
- canonical operation/service-instance architecture exists

Still missing:

- formal handler registry
- handler metadata source of truth
- handler-aware extractor routing
- explicit handler lifecycle enforcement

## Implementation Guidance

Short-term:

- keep the platform generic
- treat `travel.services` as the reference handler
- avoid creating new hardcoded runtime tables for handler-specific concepts

Near-term:

- introduce a handler registry contract
- make extraction and document extension points handler-aware
- define configuration source for handler metadata

Long-term:

- let handlers drive downstream analytics and AI enrichment paths without contaminating the transactional runtime model

## Final Contract Rule

Handlers give domain meaning.
The platform gives runtime discipline.

If a design change makes the generic platform more domain-specific than the handler boundary requires, that change should be challenged before implementation.
