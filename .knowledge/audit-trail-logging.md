# Audit Trail and Logging

Last updated: 2026-05-13

## Purpose

Define the current audit implementation, event taxonomy, and hardening boundaries for the multi-tenant architecture.

## Storage Boundary

1. Audit records are stored in control DB table: `audit_logs`.
2. Table is immutable by design (`created_at` only, no `updated_at`).
3. Every record includes HMAC signature in `signature` for tamper detection.

## Runtime Components

1. `app/Services/AuditEngine.php`
   - Central write path for audit events.
   - Adds request metadata (`method`, `host`, `path`, `url`, `route`, `origin`) under `new_values.__meta`.
   - Resolves tenant id from `currentCompany` runtime context or tenant DB mapping.
   - Fails open: if DB audit write fails, emits warning to fallback Laravel log channel.
2. `app/Traits/Auditable.php`
   - Hooks `created`, `updated`, `deleted`.
   - Sanitizes hidden model fields before write.
   - Captures reliable before/after values using `updating` snapshot + `updated` changes.

## Current Event Taxonomy

### Category: `AUTH`
1. `AUTH.LOGIN_SUCCESS`
2. `AUTH.LOGIN_FAILED`
3. `AUTH.LOGIN_LOCKED_OUT`
4. `AUTH.LOGOUT`
5. `AUTH.REGISTERED`
6. `AUTH.ACCOUNT_DELETE_REQUESTED`

### Category: `ACCESS`
1. `TENANT.ACCESS_DENIED`

### Category: `RECORD`
1. `DATA.CREATED`
2. `DATA.UPDATED`
3. `DATA.DELETED`

### Category: `USER_ADMIN`
1. `USER.ACCESS_CREATED` (user-role/membership setup)
2. `USER.ACCESS_UPDATED` (user-role/membership changes)

## Model Coverage (Enabled via `Auditable`)

Control-side models currently covered:
1. `User`
2. `Company`
3. `MainGroupCompany`
4. `Client`
5. `Module`
6. `ServiceSchema`

Not yet enabled for tenant transactional models (`Booking`, `BookingService`, `Contract`, `Passenger`, `DocumentTemplate`) to avoid cross-DB rollback divergence until after-commit strategy is introduced.

## Config

`config/audit.php`:
1. `AUDIT_ENABLED=true|false`
2. `AUDIT_FALLBACK_LOG_CHANNEL=stack|...`

## Verified Behavior

Unit tests:
1. `tests/Unit/AuditEngineTest.php::test_audit_engine_writes_event_with_signature_and_request_meta`
2. `tests/Unit/AuditEngineTest.php::test_audit_engine_falls_back_to_app_log_when_db_insert_fails`

## Known Gaps and Next Hardening Queue

1. Add verification/inspection command for historical signature checks (`audit:verify-integrity`).
2. Introduce retention + archival policy for `audit_logs` growth.
3. Add after-commit audit dispatch strategy for tenant transactional events.
4. Add actor/subject diff views in UI for operations/audit review.
5. Add query helpers/indexing review for high-volume filters (`tenant_id`, `user_id`, `action`, time range).
