# Operations Roadmap Implementation 031 Checklist

Status: `completed`
Owner: `Codex`
Created: `2026-05-15`

## Objective

Execute the architect roadmap phases in the application layer so the runtime matches the target operations platform more closely.

## Phase Execution

### Phase 1. Runtime Truth Consolidation

- [x] Stop active runtime writes to `operations.cart_payload`.
- [x] Update operation review rendering to hydrate from `service_instances`.
- [x] Keep `service_instances` as the authoritative line-level runtime record.

### Phase 2. Compatibility Freeze

- [x] Remove booking-era routes from the active tenant runtime.
- [x] Stop adding new logic to booking-era wrappers.
- [x] Keep only knowledge/history artifacts as the preserved record of the transition.

### Phase 3. Handler Contract Formalization

- [x] Add `config/handlers.php` as the active handler metadata source.
- [x] Add `HandlerRegistry` and `HandlerDefinition`.
- [x] Route capture, render, and extraction behavior through handler resolution.

### Phase 4. Document Architecture Consolidation

- [x] Remove runtime `booking.*` render aliases.
- [x] Switch active operation UI and render context to canonical `document_no`.
- [x] Move lock-state wording toward generic document lifecycle (`DocumentLocked`).

### Phase 5. Extraction And Projection Expansion

- [x] Add handler-aware extractor selection.
- [x] Add `TravelServicesOperationExtractor` as the first handler-specific extractor.
- [x] Add `operation_projections` materialization path and command.

### Phase 6. Compatibility Removal

- [x] Remove booking controller/action/model compatibility classes.
- [x] Remove booking UI wrappers and dead legacy invoice Blade fallback.
- [x] Retain only physical legacy storage remnants pending explicit archival/removal decision.

## Verification

- [x] Update targeted unit tests for canonical render/extraction behavior.
- [x] Run PHP syntax verification for changed backend files.
- [x] Run frontend production build verification.
- [ ] Full Laravel runtime tests in local host PHP.
- [ ] Tenant migration smoke pass in local host PHP.

## Remaining Deferred Debt

- Physical storage compatibility remains in `invoice_no`, `cart_payload`, `passenger_details`, and the `passengers` table.
- Module-key naming still uses `travel.booking`.
- Local runtime validation is still blocked by host PHP `8.2.5` versus required project platform `>= 8.4.0`.
