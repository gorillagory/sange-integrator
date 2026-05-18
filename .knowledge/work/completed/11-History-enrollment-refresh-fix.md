# Enrollment and Refresh Fix 011 Checklist

Status: `completed`  
Owner: Codex + User  
Created: 2026-05-13

## Tasks

- [x] Trace user enrollment flow (request validation, controller write path, DB state).
- [x] Identify and fix enrollment blockers for system-level users.
- [x] Ensure new enrollments are visible immediately in user list UI.
- [x] Ensure company create/update modals force immediate list refresh.
- [x] Add missing company search backend filter handling.
- [x] Validate syntax on changed backend files.

## Verification Notes

1. DB inspection showed only 1 row in `control.users` before this fix window, confirming enrollment did not persist.
2. PHP syntax checks passed for all changed backend files.
3. Frontend modals now trigger explicit `router.reload()` on success for deterministic immediate UI refresh.
