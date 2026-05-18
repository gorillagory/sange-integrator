# Blueprint Forge Builder Restoration Checklist

Last updated: 2026-05-15
Status: completed

## Goal

Restore the richer original schema-vector builder infrastructure into the system-side Blueprint Forge so central authoring remains safe and practical.

## Checklist

- [x] Revisit the original richer schema builder infrastructure from the previous tenant schema editor.
- [x] Restore advanced field editing, drag ordering, validation controls, and preview into Blueprint Forge.
- [x] Restore pricing-unit management and document output design into Blueprint Forge.
- [x] Restore automatic blueprint/service key generation for the central forge.
- [x] Keep tenant schema pages lightweight and review-oriented.
- [x] Verify frontend compilation after the builder restoration.

## Outcome

- Blueprint Forge now uses the richer builder stack again through a shared system component:
  - advanced field editing
  - automatic field key generation
  - compiled JSON preview
  - pricing units
  - document output designer
- Central blueprint identity now safely auto-generates the schema vector key from service name + industry unless manually overridden.
- Tenant schema screens remain intentionally simplified so the authoring story stays centralized.

## Verification

- `npx vite build --outDir "$(mktemp -d)"` passed.
