# Blueprint Forge UX Clarity Adjustment Checklist

Last updated: 2026-05-15
Status: completed

## Goal

Correct the Blueprint Forge user experience distortion caused by mixed light/dark surfaces and unclear labels after the builder restoration.

## Checklist

- [x] Revisit the central forge against the project UI/UX rules.
- [x] Remove confusing tenant-light visual language from the system-side builder surfaces.
- [x] Make shared schema-builder components theme-aware for the system workspace.
- [x] Tighten section and field labels so they are clearer and more operational.
- [x] Keep the richer builder infrastructure while improving hierarchy and readability.
- [x] Verify the frontend build after the UX adjustment.

## Outcome

- Blueprint Forge now uses dark, system-consistent surfaces for:
  - schema fields
  - field editor cards
  - form preview
  - document output editor
- Labels were simplified and clarified:
  - `Payload Attributes` -> `Schema Fields`
  - `UI Label` -> `Field Label`
  - `JSON Key` -> `Field Key`
  - `Data Type` -> `Field Type`
  - `UI Component` -> `Input Control`
  - `Grid Width` -> `Width`
  - `List Array` -> `Repeatable List`
  - `Agent UI Preview` -> `Operator Form Preview`
  - `Compiled JSON Vector` -> `Compiled Vector JSON`
- The central forge now reads like one coherent system workspace instead of mixed tenant-light cards inside a dark shell.

## Verification

- `npx vite build --outDir "$(mktemp -d)"` passed.
