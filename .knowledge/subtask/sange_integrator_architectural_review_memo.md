# Revised Architectural Review Memo
## Sange-Integrator Module Transition Plan

## Overall position

The transition direction is correct.

Moving from an overloaded `service_type` model toward a governed schema registry with explicit identity and lifecycle is the right move for a multi-tenant platform. The additive migration style in the execution draft is also the right general migration posture because it reduces tenant risk during rollout.

That said, the current execution plan is still a little too polite in the wrong places and a little too thin in a few critical runtime areas. The goal should not be a dramatic rewrite. The goal should be a **safe, boring, durable transition**.

This review focuses on the most practical adjustments needed to get there.

---

## What is already right

### 1. Standardizing schema identity is the correct direction

The target architecture is stronger because it separates service schema identity into a proper governed model instead of overloading `service_type`. This is the correct enterprise pattern and should remain the target.

### 2. The current state has real document-pipeline drift

The current-state document confirms there is split behavior today:
- `DocumentPayloadTransformer` appears orphaned
- invoice download still uses a direct Blade path
- binding style is inconsistent
- document UI still assumes older internal structures in places

That means the execution plan is not solving an imaginary problem. The drift is real.

### 3. Backend validation cannot remain structural only

The current state confirms booking `service_details` validation is only structural today, which is too weak for schema-driven runtime capture.
The target state is right to move toward schema-aware backend enforcement.

---

## Main corrections needed in the execution plan

## 1. Exact schema anchoring must be explicit, not implied

This is the most important runtime rule.

Historical bookings must always resolve against the **exact schema record used at the time of capture**, not just the latest active schema in the same family.

The execution draft currently moves in this direction, but it does not state it strongly enough. In transition language like “persist both fields,” there is still room for sloppy implementation.

### Practical correction
For each `booking_service`, persist:
- exact `service_schema_id`
- `service_code` snapshot
- `service_name` snapshot
- exact schema version used

### Why
Because if historical bookings resolve against “current active by code,” document rendering and validation will drift over time.

This is not optional. It is a hard correctness rule.

---

## 2. Exact schema anchoring alone is still not enough — use render snapshots

This is the biggest gap in many migration discussions.

Even if a booking points to the correct historical schema version, a regenerated document can still change if:
- company details change
- client details change
- auxiliary runtime data changes
- document assembly logic changes

The target-state document solves this by adding:
- `service_details`
- `service_details_extra`
- `payload_snapshot`

### Practical correction
Do not treat snapshotting as optional polish.

For official document replay and historically stable output:
- keep canonical captured values in `service_details`
- keep flexible operational values in `service_details_extra`
- keep a frozen `payload_snapshot` for rendering fidelity

### Why
This is the safest and most practical way to prevent “same booking, different regenerated document” problems later.

---

## 3. Standardize the document runtime early, but do it in a controlled sequence

The current draft is too soft when it says “decide one active rendering path.”
The current state already proves that dual rendering paths are a source of drift.
The target state is right to retire `DocumentPayloadTransformer` and move to a single `DocumentRenderContextFactory`.

### Practical correction
Do not keep the legacy Blade/vector split longer than necessary.

But also do not do a theatrical “hard cut” before the required compatibility pieces exist.

### Safer sequence
1. Add canonical schema identity/version fields non-destructively
2. Anchor bookings to exact schema records
3. Standardize canonical nested render context
4. Move document generation to the vector/dictionary pipeline only
5. Keep legacy only as temporary read-compatibility at migration boundaries
6. Remove legacy fallback code after validation

### Why
This avoids both chaos and drift.

---

## 4. Backend validation should compile into Laravel-native rules, not invent a mini validation framework

The target direction of dynamic validation is correct.
But the implementation should stay practical.

### Practical correction
Build a compiler that turns schema metadata into Laravel validation rules dynamically.

That means:
- schema in DB stores field structure and rule metadata
- runtime validator loads the selected schema
- compiler maps field definitions into standard Laravel validation arrays
- request payload is validated using Laravel-native mechanisms

### Why
This reduces risk, improves readability, and avoids maintaining a custom rule engine unless truly necessary.

### Additional practical rule
Keep extra ad hoc values outside the canonical schema-defined payload:
- `service_details` = governed fields
- `service_details_extra` = flexible fields

That preserves flexibility without polluting the formal contract.

---

## 5. Binding syntax and render context must be standardized before deeper template work

The current state already flags flat-versus-nested key mismatch as a real problem.
The target state correctly standardizes nested dot-path syntax and a canonical render context.

### Practical correction
Make this the official standard:
- `company.name`
- `booking.reference_no`
- `service.fields.flight_number`

Not:
- `company_name`
- `flight_number` with no namespace

### Migration rule
Legacy flat keys may be translated temporarily only for migration compatibility.

No new template work should be authored against flat bindings.

### Why
This is the simplest way to stop binding drift.

---

## 6. Add a binding index now, not later

This is not overengineering. It is maintenance insurance.

The target-state document adds a derived `binding_index` for templates.

### Practical correction
Keep bindings authored inside the `layout_vector`, but on save:
- extract variable references
- store a derived `binding_index`

### Why
This enables:
- compatibility checking
- schema impact analysis
- broken-template reporting
- field deprecation audits

Without this, schema evolution becomes blind and expensive.

---

## 7. Tenant overlay behavior should be declared now

The target-state architecture adds the right principle:
- control DB owns base schemas
- tenant/company overlays may adjust presentation/local behavior
- tenants do not freely fork structure by default

### Practical correction
State this clearly in the transition plan.

Allowed overlay examples:
- labels
- help text
- placeholders
- field order
- visibility
- defaults

Not allowed by default:
- field key changes
- type changes
- arbitrary structural forks

### Why
If this is not declared early, teams will eventually put tenant customizations in the wrong layer and break governance.

---

## 8. Cross-database caching is useful, but should not be treated as a first-wave architectural blocker

This point matters, but it should not dominate the early transition discussion.

### Practical correction
Do not make Redis/schema caching a prerequisite for getting the architecture right.

Instead:
- first lock the schema identity, lifecycle, render context, and runtime contracts
- then add caching as an optimization when retrieval patterns are clear

### Why
Caching an unstable contract is not progress.

This is a second-wave performance concern, not the first-wave architecture risk.

---

## Recommended adjusted execution order

This is the practical version I would use.

### Phase 1 — Canonical identity and lifecycle
- Add `service_code`, version, status, and related governance fields in control DB
- Backfill existing schema records
- Preserve old fields temporarily for compatibility
- Explicitly define exact-schema anchoring rules for runtime

### Phase 2 — Booking runtime hardening
- Persist exact `service_schema_id`
- Persist schema/version snapshots needed for historical replay
- Add `service_details`, `service_details_extra`, and `payload_snapshot`
- Keep old fields only as transitional compatibility where needed

### Phase 3 — Render contract standardization
- Standardize nested dot-path bindings
- Introduce `DocumentRenderContextFactory`
- Move preview/generation to the canonical render context
- Keep legacy render logic only as temporary migration boundary support

### Phase 4 — Backend validation
- Introduce DB-driven schema rule compilation into Laravel-native validation rules
- Enforce governed fields in `service_details`
- Keep flexible fields isolated in `service_details_extra`

### Phase 5 — Template compatibility tooling
- Add derived `binding_index`
- Add compatibility checks and broken-binding reporting
- Ensure only compatible templates are selectable

### Phase 6 — Cleanup
- remove `service_type` from critical logic and constraints
- remove `DocumentPayloadTransformer`
- remove legacy Blade fallback paths
- remove flat-key compatibility once migration is complete

---

## Final balanced position

The current transition plan is not wrong. It is just slightly under-specified in the places that matter most for long-term stability.

The most practical improvements are:
- explicitly anchor every booking to the exact schema record used
- add frozen render snapshots, not just schema identity
- standardize one render context and one forward document pipeline
- compile schema validation into Laravel-native rules
- introduce binding indexing for compatibility management
- define tenant overlay boundaries now
- treat caching as a later optimization, not an early architectural crutch

That is the efficient path:
not dramatic, not theoretical, and not fragile.
