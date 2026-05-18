# Booking Compatibility Deprecation Plan

Last updated: 2026-05-15

## Purpose

This document defines how booking-era compatibility should be phased out after the operations generalization.

## Policy

Booking compatibility exists only to protect transition safety.

It is not canonical architecture.

No new feature work should target booking compatibility surfaces.

## Current Status

Application-layer booking compatibility has now been removed from the active runtime.

Completed removals:

- booking routes
- booking controller/action/model wrappers
- booking UI wrappers
- render-time `booking.*` payload aliases

Remaining compatibility debt is now physical-storage and naming debt only:

- `operations.cart_payload`
- `operations.passenger_details`
- `passengers` table
- tactical persistence field `invoice_no`

## Compatibility Inventory

### 1. Route compatibility

Current surfaces:

- none in active runtime

Current behavior:

- removed

### 2. Controller compatibility

Current surface:

- removed

### 3. Action compatibility

Current surface:

- removed

### 4. Model compatibility

Current surfaces:

- removed

### 5. Document payload compatibility

Current surface:

- removed

### 6. UI compatibility wrappers

Current surfaces:

- removed

Current behavior:

- removed

### 7. Legacy columns and tables

Current surfaces:

- `operations.cart_payload`
- `operations.passenger_details`
- `passengers` table
- tactical document field `invoice_no`

## Deprecation Stages

### Stage A. Keep Temporarily

Keep for transition:

- none at the application layer

Purpose:

- protect saved links, existing habits, and active templates during migration

### Stage B. Migrate Consumers

Migrate all active consumers to canonical surfaces:

- URLs -> `/operations`
- runtime docs -> `operation` / `service_instances`
- templates -> canonical payload roots
- developer code -> operation/service-instance classes only

### Stage C. Freeze

Once migration begins:

- do not extend booking compatibility
- do not document booking compatibility as canonical
- do not introduce new booking-named fields

### Stage D. Remove

Remove compatibility only after explicit criteria are met.

## Removal Conditions

### Booking routes

Remove when:

- internal navigation no longer links to `/bookings`
- external known consumers are migrated
- tenant-facing operational guides point only to `/operations`

### Booking controller/action/model wrappers

Remove when:

- application code no longer references them
- search confirms no active imports/usages outside archive/history docs

### Booking UI wrappers

Remove when:

- router/navigation uses only canonical operations pages
- no page-level references remain that expect booking props or booking URLs

### `booking.*` document alias

Remove when:

- active document templates have been reviewed
- active templates no longer bind to `booking.*`
- author documentation has been updated and adopted

### `cart_payload`

Remove or demote when:

- all runtime review pages render directly from `service_instances`
- no business logic relies on `cart_payload`
- any remaining use is explicitly documented as derived cache

### `passenger_details` and `passengers`

Remove only when:

- historical tenant data no longer requires direct legacy reads
- no template, export, or admin tool still references legacy passenger structures
- archival or migration decision for historical records has been made

### `invoice_no`

Rename or supersede only when:

- document lifecycle model is formalized beyond invoice-centric usage
- migration path is defined for existing data and documents

## Recommended Timing

### Immediate

- freeze booking compatibility
- migrate documentation and new work to canonical terms only

### Near-term

- verify there are no tenant-authored templates outside current source control still using `booking.*`
- eliminate or formally archive any remaining dependence on `cart_payload`

### Later

- remove booking wrappers
- remove booking routes
- make final decision on legacy passenger storage and tactical field naming

## Communication Rules

When communicating internally:

- refer to current runtime as `operations`
- refer to line-level truth as `service_instances`
- refer to booking compatibility as legacy transition support

Do not present booking compatibility as current architecture.

## Deprecation Success Criteria

Booking compatibility is considered fully deprecated only when:

1. canonical routes, pages, models, and documents are the only active development surface
2. legacy aliases are no longer needed by active templates or runtime flows
3. removal does not break historical traceability because knowledge/history artifacts remain preserved
4. any remaining physical storage remnants have an explicit archival or removal decision
