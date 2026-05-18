# Blueprint Forge / Tenant Schema Frontend Simplification Checklist

Last updated: 2026-05-15
Status: completed

## Goal

Reduce control-plane complexity in the frontend by making the split explicit:

- `Blueprint Forge` stays the central authoring surface for master schema vectors.
- tenant schema pages become lightweight review surfaces instead of acting like a second full editor.

## Checklist

- [x] Reframe tenant schema index into a lightweight `Schema Vector Manager`.
- [x] Remove tenant-side create/delete editing cues from the schema index UI.
- [x] Replace tenant schema builder behavior with a review-first experience.
- [x] Add clear guidance in tenant UI that structural changes belong in Blueprint Forge.
- [x] Update Blueprint Forge copy so it is clearly positioned as the single source of truth.
- [x] Verify frontend compilation after the UI simplification pass.

## Outcome

- Tenant schema index now emphasizes review of published vectors rather than authoring.
- Tenant schema detail route now functions as a read-only review/preview page for published vector contracts.
- Tenant create-mode now explains that schema authoring is centralized rather than presenting another full editor.
- Blueprint Forge copy now explicitly frames central ownership of structure, versioning, and lifecycle.

## Verification

- `npx vite build --outDir "$(mktemp -d)"` passed.

## Deferred

- Company-scoped activation/default controls are still not implemented. Tenant schema UI is intentionally read-only until company-level activation can be modeled safely.
