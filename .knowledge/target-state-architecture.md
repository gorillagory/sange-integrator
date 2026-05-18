# Target-State Architecture

Last updated: 2026-05-15

## Purpose

This document defines the intended final architecture for the dynamic operations platform.

It is a **decision document**, not a reflection document.

## Architectural Intent

The platform shall operate as a **dynamic operational capture and document handling system** where:

1. runtime capture is vector-driven
2. commercial values are captured at the same point as operations
3. documents are rendered from canonical runtime payloads
4. analytics and business-specific models are downstream projections

The runtime shall not be designed around fixed domain-specific child models such as passengers, patients, or any one industry-specific entity unless those models are later justified by cross-workflow reporting or orchestration needs.

## Canonical Runtime Nouns

### Operation

Definition:

- the parent workflow/commercial envelope

Responsibilities:

- company context
- client context
- contract context
- handler context
- workflow status
- aggregate totals
- document routing state

The `operation` is not the primary operational truth holder for service-specific data.

### Service Instance

Definition:

- the primary runtime operational record

Responsibilities:

- vector/schema identity
- governed dynamic payload
- extension payload
- commercial values
- immutable capture snapshot

The `service_instance` is the **authoritative line-level source of truth**.

### Handler

Definition:

- the runtime contract that defines how a family of service vectors behaves

Examples:

- `travel.services`
- future non-travel handlers

The handler is the boundary between:

- generic platform runtime
- workflow-specific operational semantics

### Schema / Vector

Definition:

- the governed structure that defines the fields, validation, and payload shape for a service instance

The schema is a control-plane asset.

### Document Output

Definition:

- a rendered artifact derived from operation context plus service instance payloads

Examples:

- invoice
- quote
- receipt
- future operational documents

### Projection / Extraction

Definition:

- the downstream transformation layer that converts runtime payloads into reportable, analyzable, or AI-enrichable forms

## Source Of Truth Decisions

### Decision 1. Line-level source of truth

The single authoritative line-level operational source of truth is:

- `service_instances`

Implications:

- `operations.cart_payload` is not authoritative
- any duplicate line-level representation must be treated as derived cache only

### Decision 2. Dynamic field source of truth

Dynamic service-specific fields must live in:

- `service_details`
- `service_details_extra` when explicitly allowed

They must not be introduced as new runtime columns or child tables by default.

### Decision 3. Document payload source of truth

Documents must be rendered from canonical payload roots:

- `operation`
- `services`
- `service_instances`
- `finance`
- `client`
- `company`

Compatibility aliases may exist only as transitional adapters.

### Decision 4. Analytics source of truth

Analytics must be derived from:

- extracted `service_instance` payloads
- operation envelope metadata

Analytics must not dictate runtime storage design.

## Target Runtime Layers

### 1. Capture Layer

Inputs:

- handler
- schema/vector
- operation envelope data
- service payload data
- finance values

Outputs:

- one `operation`
- one or more `service_instances`
- immutable payload snapshots

### 2. Document Layer

Inputs:

- operation context
- service instance context
- company/client context
- finance values
- document template

Outputs:

- rendered document payload
- preview
- downloadable output

### 3. Projection Layer

Inputs:

- operation
- service instance
- handler rules

Outputs:

- normalized extraction rows
- materialized analytics records
- future AI enrichment outputs

## Data Model Target

### Operation target contract

Keep lightweight:

- `company_id`
- `client_id`
- `contract_no`
- `reference_no`
- `handler_key`
- workflow status
- aggregate totals
- document lifecycle summary

Transitional fields may remain temporarily, but they do not define the final model.

### Service instance target contract

Required:

- `operation_id`
- `company_id`
- `service_schema_id`
- `service_code`
- `schema_version`
- `service_name`
- `service_details`
- `service_details_extra`
- `qty`
- supplier/commercial fields
- `payload_snapshot`

Optional handler-specific enrichment must remain downstream unless proven to be cross-cutting runtime infrastructure.

## Document Architecture Target

### Canonical authoring contract

Template authors should build against:

- `operation.*`
- `services[*].*`
- `service_instances[*].*`
- `finance.*`
- `client.*`
- `company.*`

### Compatibility rule

`booking.*` aliases are transitional only.

They must not be treated as canonical contract in new templates or new documentation.

### Future document model

The platform should evolve toward explicit document outputs rather than only invoice-centric state.

Target conceptual model:

- operation
- document templates
- document outputs
- document lifecycle

This does not require immediate new tables, but it should guide future implementation.

## Handler Architecture Target

The platform runtime remains generic.
Handlers provide the workflow-specific contract.

Target split:

- platform owns capture engine, document engine, extraction engine, tenancy, security
- handler owns schema family, runtime semantics, extraction semantics, document conventions

## Analytics Architecture Target

### Extraction stage

Normalize runtime payloads into:

- dimensions
- metrics
- raw payload
- handler identity

### Materialization stage

Persist projection-friendly structures for:

- dashboards
- filtering
- time series
- cross-operation aggregation

### Intelligence stage

Future AI panels should consume:

- extracted payloads
- materialized projections
- handler-aware enrichments

Not raw transactional runtime state alone.

## Architectural Boundaries

### What belongs in runtime

- capture
- validation
- snapshots
- workflow status
- document generation inputs
- finance-at-source values

### What belongs downstream

- business-specific reporting models
- heavy summarization
- classification
- enrichment
- AI interpretation

## Final Target Verdict

The target architecture is:

- vector-first
- service-instance authoritative
- operation-envelope based
- handler-governed
- document-capable
- projection-driven for analytics

Any future implementation work should be measured against this target before introducing new models, tables, or compatibility behavior.
