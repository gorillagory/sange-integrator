# Dynamic Operations Generalization 027 Checklist

Status: `completed`
Started: `2026-05-15`
Completed: `2026-05-15`

## Scope

Refactor the booking-centric runtime into a generic operations capture flow with service-instance payloads as the primary operational truth, remove passenger-driven active behavior, and add normalized extraction scaffolding.

## Checklist

- [x] Create rename/generalization migration for `bookings` -> `operations` and `booking_services` -> `service_instances`.
- [x] Add `handler_key` to parent operation storage and backfill existing records with `travel.services`.
- [x] Introduce canonical `Operation` and `ServiceInstance` runtime models and update active runtime queries/relations.
- [x] Replace booking controller/routes/pages with canonical operations controller/routes/pages.
- [x] Add temporary booking-to-operations compatibility routes for one transition phase.
- [x] Remove passenger validation, active UI capture, writes, and runtime reads from the active operations flow.
- [x] Update service create/review flows so dynamic operational data comes only from service vectors.
- [x] Update document render context to use canonical operation/service payload roots and keep one-phase `booking.*` compatibility aliases.
- [x] Add analytics extraction scaffolding with normalized output rows and a dry-run console entrypoint.
- [x] Update reports, tenant navigation, schema copy, and active knowledge docs to match the generalized runtime.
- [x] Add targeted unit coverage for extraction output and canonical document payload compatibility.

## Verification

- [x] `php -l` passed for all changed PHP files in the operations/runtime/document/extraction sweep.
- [x] `npx vite build --outDir "$(mktemp -d)"` passed for the updated Inertia/Vue pages and preview payload code.
- [ ] `php artisan test tests/Unit/OperationExtractionManagerTest.php tests/Unit/DocumentRenderContextFactoryTest.php tests/Unit/DocumentTemplateCompatibilityServiceTest.php`

## Blockers

- Local CLI test execution is blocked by an environment mismatch: Composer platform checks require PHP `>= 8.4.0`, but the current shell runtime is PHP `8.2.5`.

## Notes

- The active runtime now uses `/operations` and `OperationController`, while `/bookings` remains only as a temporary compatibility surface.
- Passenger persistence remains physically present only for legacy read compatibility; new runtime writes no longer depend on it.
