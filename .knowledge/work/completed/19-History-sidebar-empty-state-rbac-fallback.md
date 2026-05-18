# Sidebar Empty State RBAC Fallback - 019

Status: in_progress

## Checklist

- [x] Inspect `SystemLayout` and `TenantLayout` nav rendering conditions.
- [x] Add defensive fallback when authenticated user receives empty/all-false RBAC nav payload.
- [x] Run frontend static sanity check.
- [x] Move checklist to `completed`.

## Verification Notes

- Fallback is UI-only. Backend route middleware remains the source of truth for access control.
- Updated nav computation compiles structurally (no script syntax errors introduced in modified Vue files).

Status: completed
