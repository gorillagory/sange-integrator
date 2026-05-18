# Architecture

Last updated: 2026-05-15

## Product Shape

Sange Integrator is a **dynamic company operations platform**.

It is not primarily a fixed booking, patient, or departmental system.  
Its runtime responsibility is to:

1. capture operational data
2. handle operational documents
3. capture commercial values at the same point of operation

The platform should remain flexible enough for different operational domains without requiring a new rigid model for every workflow.

## Core Architectural Principle

The runtime should understand:

- company context
- handler/module context
- vector/schema identity
- structured payload data
- pricing and commercial values
- document generation inputs

The runtime should **not** depend on domain-specific child models unless there is a strong, proven need.

Examples of things that should usually live in vector payloads instead of dedicated runtime tables:

- passenger fields
- patient intake fields
- guest details
- service-specific operational attributes

## Runtime Layers

### 1. Operational Capture Layer

Purpose:
- render vector-defined forms
- accept dynamic structured payloads
- store those payloads with strong identity and version context
- support documents and operational follow-up

This layer is optimized for:
- flexibility
- controlled schema evolution
- replay-safe data capture

### 2. Document Handling Layer

Purpose:
- build document templates
- bind nested payload paths
- generate previews and final outputs from captured runtime payloads

This layer must consume:
- company context
- client context
- dynamic service payloads
- finance values captured during operations

### 3. Projection / Analytics Layer

Purpose:
- extract meaning from captured operational payloads
- generate business-specific dashboards
- materialize analytics tables or AI-assisted panels later

This layer is downstream.  
It must not dictate the runtime capture model too early.

## Multi-Tenant Boundary

### Control Plane

- fixed DB connection: `control`
- fixed physical database: `sange_control`
- owns users, companies, memberships, modules, vectors, and governance data

### Tenant Plane

- dynamic DB connection: `tenant`
- physical DB selected from the active company record
- owns operational records, document templates, contracts, and workflow data

## Request Flow

1. User authenticates.
2. System routes run on `sys.bayam.test`.
3. Tenant routes run on `{subdomain}.bayam.test`.
4. `IdentifyTenant` resolves active company, validates access, sets tenant DB, and shares company context.
5. Module and role middleware apply.
6. Runtime pages load vectors and persist dynamic payload-bearing operational records.

## Current Runtime Unit

The important runtime unit is the **service instance**, not child models like passenger/patient/etc.

In the current runtime this is represented by `service_instances`, and the concept is broader than any one module:

- a service instance is a vector-driven operational record
- it stores governed fields and commercial values
- it can feed documents immediately
- it can feed analytics later

## Finance In Operations

Operations capture is also finance-aware.

The runtime must support values such as:

- qty
- unit cost / supplier cost
- discount
- tax
- markup
- client charge
- line total

These are not secondary concerns. They are part of the operational truth captured at source.

## Architectural Guardrails

1. Prefer vector-driven payload capture over new hardcoded runtime tables.
2. Keep schema identity explicit:
   - `service_schema_id`
   - service code
   - schema version
3. Preserve snapshots when replay/document stability matters.
4. Keep control/tenant boundaries strict.
5. Treat analytics and business-specific panels as downstream projections, not the source of runtime truth.
