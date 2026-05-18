# Audit Trail / Logging Finalization Draft (Simple Log Segment)

Last updated: 2026-05-13

## Objective

Finalize the current audit trail baseline with a simple **log segment** layer so operations can filter and review logs quickly without changing the existing event taxonomy.

## Scope (Simple Version)

1. Keep current `category` + `action` contract intact.
2. Add one new dimension: `segment`.
3. Use `segment` for coarse operational grouping/filtering.
4. Do not introduce retention pipelines, async streaming, or SIEM integration in this phase.

## Proposed Segment Model

Recommended segment values:
1. `AUTH`
2. `ACCESS`
3. `USER_ADMIN`
4. `DATA`
5. `SYSTEM`

Mapping rule (simple):
1. If category is `AUTH`, segment = `AUTH`.
2. If category is `ACCESS`, segment = `ACCESS`.
3. If category is `USER_ADMIN`, segment = `USER_ADMIN`.
4. If category is `RECORD`, segment = `DATA`.
5. Else segment = `SYSTEM`.

## Data Model Changes

Control DB (`audit_logs`):
1. Add `segment` string column (`nullable` for migration safety).
2. Backfill existing rows using `category` mapping.
3. Add index: `audit_logs_segment_created_at_idx` on (`segment`, `created_at` desc).

## Runtime Changes

`AuditEngine::log(...)`:
1. Add optional argument `?string $segment = null`.
2. Resolve final segment:
   - explicit argument wins
   - fallback to category mapping helper
3. Persist `segment` into `audit_logs`.
4. Include segment in signature payload to preserve tamper consistency.

Helper:
1. `AuditEngine::resolveSegment(string $category, string $action, ?string $segment): string`

## Query / API Surface (Minimal)

If/when an audit list endpoint is added:
1. Optional filters:
   - `segment`
   - `category`
   - `action`
   - `tenant_id`
   - `user_id`
   - date range
2. Default order: newest first by `created_at`.

No complex UI required in this phase; simple filter chips/dropdown by `segment` is enough.

## Rollout Plan

1. Migration:
   - add `segment`
   - backfill existing data
   - add index
2. Service:
   - update `AuditEngine`
   - keep backward compatibility for all current call sites
3. Verification:
   - unit tests for segment resolution and persistence
   - integrity signature test updated for segment inclusion
4. Optional:
   - add a read-only audit list page with segment filter

## Acceptance Criteria

1. New audit records always have non-empty `segment`.
2. Existing records are backfilled with expected segment values.
3. Existing log call sites continue to work without modification.
4. Segment filter query returns expected subsets quickly.
5. Signature verification remains valid after segment addition.

## Deferred (Out of Scope for This Draft)

1. Retention/archival policy execution.
2. After-commit tenant transaction auditing.
3. External log sink forwarding (ELK/Datadog/Splunk).
4. Alerting rules and incident automation.
