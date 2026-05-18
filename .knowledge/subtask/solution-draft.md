# Solution Draft: Execution Plan for Module-Building Alignment

Last updated: 2026-05-12

Historical note:
- This is a preserved planning artifact.
- Current active direction now lives in `.knowledge/architecture.md` and `.knowledge/data-model.md`.

## 1) Comparison Summary

Compared files:
- `.knowledge/archive/design/module-building-document.md` (current-state, code-verified)
- `.knowledge/subtask/respond-module-building-document.md` (target-state proposal)

### Aligned
1. Strict control vs tenant separation.
2. Need to remove document generation ambiguity.
3. Need stronger schema governance and safer evolution.

### Extended in proposal (not yet implemented)
1. Shift from `service_type` to `service_code` + `service_name`.
2. Explicit schema version lifecycle (`draft`, `active`, `deprecated`, `archived`).
3. Backend schema-aware validation for booking payload fields/types/rules.
4. Formalized schema-to-render binding contract.

### Current discrepancies requiring execution
1. DB uniqueness mismatch: global unique `service_type` vs industry-scoped controller validation.
2. Orphaned/legacy `DocumentPayloadTransformer` contract.
3. Incomplete wiring of schema `document_output` into active generation path.
4. UI metric drift (`Document Index` block counter reads outdated structure).
5. Historical booking replay is not yet guaranteed to anchor to exact schema/version + frozen render payload.

## 2) Execution Strategy

Use additive migration and phased cutover. Do not do breaking rename in one step.

Non-negotiable correctness rules:
1. Historical booking records must resolve against the **exact schema record/version used at capture time**.
2. Official document replay must use frozen payload snapshots, not re-hydrated mutable runtime state.
3. New template/binding work must use nested dot-path bindings only.
4. Backend schema validation must compile into Laravel-native validation rules.
5. `service_schema_id` is the authoritative runtime anchor; `service_code`, `service_name`, and version stored on booking records are historical snapshots.
6. Legacy rendering path is read-compatibility only during migration; no new feature or generation entrypoint may be added to legacy path.

Authoritative policy locks:
1. Uniqueness rule: `service_code` must be unique per `industry` per schema `version` (composite unique: `industry`, `service_code`, `version`).
2. Active default rule: at most one `is_default = true` per (`industry`, `service_code`) among `status = active` rows.

Dual-write scope during transition:
1. Control schema identity:
   - continue writing `service_type` for backward compatibility
   - write canonical `service_code`, `service_name`, `version`, `status`, `is_default`
2. Booking runtime and historical snapshot fields:
   - write `service_schema_id` (authoritative link)
   - write `service_code`/`service_name`/`schema_version` snapshots
   - write `service_details`, `service_details_extra`, `payload_snapshot`
3. API/UI response models:
   - expose both legacy and canonical fields during migration
   - canonical fields are primary in new code paths
   - remove legacy fields only in final cleanup phase

## 3) Phased Plan

### Phase 0: Contract Freeze and Compatibility Rules
Deliverables:
1. Lock document contracts for `schema_payload` and `layout_vector` in docs.
2. Define dual-key transition policy:
   - read by `service_code` if present, fallback to `service_type`.
   - write both during transition.
3. Finalize rollout guardrails and rollback checkpoints.
4. Declare tenant overlay boundaries (allowed vs disallowed customizations).

Acceptance:
- Team agrees on transition contract before code migration.

### Phase 1: Canonical Identity and Lifecycle (Non-breaking)
Deliverables:
1. Control DB migration for `service_schemas`:
   - add `service_code` (nullable initially)
   - add lifecycle/version fields (`version`, `status`, `is_default`, optional schema family key)
   - add composite unique index: (`industry`, `service_code`, `version`)
   - enforce single active default per (`industry`, `service_code`)
2. Backfill `service_code` from existing `service_type`.
3. Keep existing `service_type` intact.
4. Resolve current mismatch between DB uniqueness and controller uniqueness scope.

Acceptance:
- Existing schema CRUD still works.
- New columns populated for all existing rows.

### Phase 2: Booking Runtime Hardening (Exact Anchoring + Snapshots)
Deliverables:
1. Persist exact schema anchor in each `booking_service`:
   - `service_schema_id`
   - schema version snapshot used
   - `service_code` snapshot
   - `service_name` snapshot
2. Split runtime payload concerns:
   - `service_details` for governed schema fields
   - `service_details_extra` for flexible operational fields
3. Add and persist `payload_snapshot` for replay-stable rendering.
   - Snapshot event rule:
     - create immutable booking snapshot at booking commit
     - create immutable issuance snapshot at each official document issuance event (invoice/re-issue/versioned output)
   - Immutability rule:
     - snapshots are append-only
     - corrections produce a new snapshot record/version, never in-place mutation
4. Keep legacy fields only as temporary compatibility bridges.

Acceptance:
- Historical booking replay is schema-stable.
- New bookings still work during transition without tenant disruption.

### Phase 3: Render Contract Standardization (Single Forward Path)
Deliverables:
1. Standardize binding syntax to nested dot-path conventions:
   - e.g. `company.name`, `booking.reference_no`, `service.fields.flight_number`
2. Introduce a canonical `DocumentRenderContextFactory`.
3. Move preview and generation to the canonical vector/dictionary pipeline.
4. Keep legacy rendering logic only as temporary migration-boundary compatibility.
   - No new generation flow, feature work, or template enhancement is allowed on the legacy path.

Acceptance:
- Preview and generation share one canonical render context contract.
- No new template authored against flat keys.

### Phase 4: Schema-aware Backend Validation (Laravel-native Compilation)
Deliverables:
1. Add validator compiler that converts DB schema metadata into Laravel validation rule arrays.
2. Enforce governed rules on `service_details` during booking create/update.
3. Keep ad hoc values isolated in `service_details_extra`.
4. Return deterministic field-level errors in request responses.

Acceptance:
- Invalid governed payloads are rejected server-side.
- Validation remains readable and maintainable using Laravel-native primitives.

### Phase 5: Template Compatibility Tooling
Deliverables:
1. Add derived `binding_index` on template save (extracted from `layout_vector`).
2. Add compatibility checks:
   - schema impact analysis
   - broken-binding reporting
   - deprecation warnings
3. Restrict template selection where compatibility fails.

Acceptance:
- Template/schema compatibility is observable and enforceable.

### Phase 6: Cleanup and Decommissioning
Deliverables:
1. Remove hard dependency on `service_type` from critical app logic and constraints.
2. Remove `DocumentPayloadTransformer` once canonical path is fully active.
3. Remove legacy Blade fallback path for production document generation.
4. Remove flat-key compatibility translation after migration window.
5. Fix UI drift issues (e.g., document index block count based on `header/body/footer` traversal).

Acceptance:
- All primary flows run on canonical identity + canonical render context.
- Legacy compatibility branches are removed.

## 4) Work Breakdown (Concrete Targets)

Primary file groups to touch:
1. Control migrations + schema model/controller:
   - `database/migrations/control/*service_schemas*`
   - `app/Models/ServiceSchema.php`
   - `app/Http/Controllers/Admin/SchemaController.php`
2. Booking runtime:
   - `app/Http/Controllers/BookingController.php`
   - `app/Actions/Travel/CreateBookingAction.php`
   - `resources/js/Pages/Bookings/Create.vue`
   - `resources/js/Pages/Bookings/Components/ServiceRow.vue`
3. Rendering/document pipeline:
   - `app/Services/DocumentPayloadTransformer.php`
   - `app/Services/PdfCompilerService.php`
   - `app/Http/Controllers/Admin/DocumentTemplateController.php`
4. UI drift fix:
   - `resources/js/Pages/Admin/Documents/Index.vue`
5. New/updated runtime support services (expected):
   - schema validation compiler service
   - `DocumentRenderContextFactory`
   - template binding extraction/indexing service

## 5) Risk Controls

1. Use phased feature flags for key-path switching (`service_type` -> `service_code`).
2. Keep reversible migrations until post-cutover verification.
3. Run seed + booking creation + document preview regression at each phase.
4. Snapshot tenant/control DB before destructive cleanup.
5. Treat caching as second-wave optimization; do not block first-wave contract hardening on cache design.
6. Add replay regression tests: same booking must regenerate same output across code deployments.

## 6) Tenant Overlay Guardrail

Allowed overlay changes (tenant/local):
1. Labels
2. Help text
3. Placeholders
4. Field order
5. Visibility
6. Default values

Disallowed by default:
1. Field key changes
2. Type changes
3. Arbitrary structural forks

## 7) Done Criteria

1. Schema identity model is explicit and consistent across DB, API, and UI.
2. Every booking service is anchored to exact schema record/version and has replay-safe snapshots.
3. Booking payloads are validated against schema contracts via Laravel-native compiled rules.
4. Document preview and generated documents use one canonical nested-key render context.
5. Binding compatibility can be audited via derived `binding_index` and checks.
6. All discrepancies listed in `module-building-document.md` section 8 are resolved or formally deprecated.

## 8) Execution Status (2026-05-13)

Implemented phases:
1. Phase 1 complete (canonical schema identity/lifecycle scaffolding + dual-write compatibility).
2. Phase 2 complete (booking schema anchoring and payload snapshot foundations).
3. Phase 3 complete (canonical render context and unified forward document pipeline).
4. Phase 4 complete (schema-aware backend validation via Laravel-native compiled rules).
5. Phase 5 complete (binding index extraction and template compatibility analysis/reporting).
6. Phase 6 complete (critical-path cleanup: canonical anchors enforced, legacy transformer retired, legacy invoice fallback removed).

Environment verification summary:
1. New unit tests for Phase 4/5 pass in Sail runtime (PHP 8.5).
2. Control migration dry-run succeeds in Sail.
3. Tenant migration dry-run currently blocked by tenant DB config in container (`database "sail" does not exist` on tenant connection).

Remaining non-code action:
1. Fix tenant DB connection/runtime configuration in target environment, then run tenant migration and booking/document smoke tests end-to-end.

### Additional Recommendations Status

All additional recommendations have been incorporated into sections:
- `Execution Strategy` (non-negotiable correctness rules, authoritative policy locks, dual-write scope)
- `Phase 1` (explicit uniqueness/version governance)
- `Phase 2` (snapshot lifecycle and immutability policy)
- `Phase 3` (strict legacy rendering boundary)

### Cross-Module Hardening (2026-05-13)

1. Audit trail baseline has been implemented as a parallel hardening lane:
   - central `AuditEngine` resilience + request meta capture
   - auth/access/user-admin event logging
   - control-model `Auditable` coverage
2. Reference: `.knowledge/audit-trail-logging.md`.

### Operational Baseline Follow-up (2026-05-14)

1. Local environment defaults were normalized to the active multi-tenant runtime:
   - `DB_CONNECTION=control`
   - control database `sange_control`
   - local app port `8080`
   - Vite port `5174`
2. Root and knowledge setup docs now reflect the current Sail bootstrap flow and domain map.

### Booking Module Follow-up (2026-05-15)

1. Booking create/invoice flows now reject contract selections that do not belong to the active tenant company and selected client.
2. Passenger roster capture is now exposed in booking creation and reflected in booking review, aligning the UI with the existing tenant `passengers` persistence path.
3. Tenant reports now have an implemented `Reports/Index` page backed by booking metrics and recent activity data.

### Operations Generalization And Architecture Reassessment (2026-05-15)

1. The active runtime has been generalized toward `operations` and `service_instances`, with service payloads treated as the primary operational truth.
2. Architect-facing documentation has been added for the current target state, transition risks, and next decision points.
3. Separate service handling flow and mind map artifacts now describe how runtime capture, documents, and extraction fit together.

### Strict Architect Package (2026-05-15)

1. A target-state architecture document now defines canonical runtime nouns, source-of-truth rules, and the intended operational/document/projection split.
2. A phased transition roadmap now sequences consolidation work from runtime truth cleanup through compatibility removal.
3. Booking-era compatibility now has an explicit deprecation plan with inventory, stages, and removal conditions.
4. A handler contract specification now formalizes `handler_key`, lifecycle, runtime responsibilities, and future extension points.

### Architect Roadmap Execution (2026-05-15)

1. Operation review and document generation now read canonical line truth from `service_instances` rather than `cart_payload`.
2. Booking compatibility routes, wrappers, and `booking.*` render aliases have been removed from the active application layer.
3. Handler registration is now explicit through `config/handlers.php` and the handler registry service.
4. Extraction is now handler-aware, and a first materialized downstream projection table/command has been added for analytics staging.
