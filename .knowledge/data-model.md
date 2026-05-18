# Data Model

Last updated: 2026-05-15

## Modeling Direction

The application is moving toward a **dynamic capture model**.

That means:

1. operational data is captured through vectors/schemas
2. runtime records store structured payloads plus identity/version context
3. domain-specific analytics models come later through extraction/projection

So the main question is not:

- “what fixed table should this field belong to?”

The main question is:

- “what vector defines this data, and where should the payload snapshot live?”

## Database Zones

### Control DB

Connection: `control`  
Database: `sange_control`

Purpose:
- identity
- tenant registry
- memberships
- module enablement
- schema/vector governance
- global/shared metadata

Key tables:
- `users`
- `companies`
- `company_user`
- `modules`
- `company_module`
- `service_schemas`
- `global_clients`
- `main_group_companies`
- `audit_logs`
- permission tables
- Laravel infrastructure tables

### Tenant DB

Connection: `tenant`  
Database: resolved dynamically from active company

Purpose:
- operational capture
- document storage
- local workflow records
- client contracts

Key tables:
- `contracts`
- `document_templates`
- `operations`
- `service_instances`

## Current Operational Pattern

### Envelope Record

`operations` now acts as the workflow/commercial envelope:

- client
- contract
- status
- invoice state
- aggregate totals

This kind of parent record can continue to exist where useful, but it should remain lightweight.

### Service Instance Record

`service_instances` is the more important runtime concept.

It should be treated as the operational truth holder:

- vector/schema identity
- schema version
- governed service payload
- extra payload
- pricing fields
- totals
- snapshots

In broader platform terms, this is the pattern to preserve:

- one parent operation can contain one or more vector-driven service instances
- the service instance is the dynamic record that matters most

## Dynamic Payload Contract

Service-instance records should keep enough structure to be replayable and interpretable later.

Core fields:

- `service_schema_id`
- `service_code`
- `schema_version`
- `service_name`
- `service_details`
- `service_details_extra`
- pricing fields
- `payload_snapshot`

## Deprecated / De-emphasized Thinking

The platform should avoid overcommitting to domain-specific child tables too early.

Examples:

- passenger tables
- patient-specific runtime tables
- service-specific micro-models created only to mirror vector fields

Those can become downstream projections later if analytics or operational tooling truly needs them.

## Projection Principle

Analytics and specialized business panels should be built from extracted payloads, not by forcing runtime storage into rigid domain models too early.

This means:

1. runtime storage stays vector-first
2. analytics can build proper reporting models later
3. AI-assisted panels can classify, group, enrich, and summarize dynamic payloads downstream

## Practical Rule

If a new field only exists because one operational vector needs it, it should usually live in the service payload.

If many workflows later require normalized querying/reporting on the same concept, then create a projection or materialized model later.
