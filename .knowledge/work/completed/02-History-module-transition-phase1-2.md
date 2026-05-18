# Module Transition Phase1-2 002 Checklist

Status: `completed`  
Owner: Codex + User  
Created: 2026-05-12

## Tasks

- [x] Add control DB migration for canonical service schema identity/lifecycle fields and index policy.
- [x] Update `ServiceSchema` model for canonical + dual-write fields/casts.
- [x] Update admin/system schema controllers for canonical validation and dual-write behavior.
- [x] Update admin schema builder/index UI to use canonical fields with compatibility fallback.
- [x] Add tenant DB migration for booking service schema/version snapshots and payload snapshot fields.
- [x] Update booking models/actions/controllers to persist schema anchors and snapshots.
- [x] Update booking create UI flow to submit canonical key with fallback compatibility.
- [x] Run focused verification (lint/syntax checks) and finalize checklist.
