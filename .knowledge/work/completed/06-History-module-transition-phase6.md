# Module Transition Phase6 006 Checklist

Status: `completed`  
Owner: Codex + User  
Created: 2026-05-12

## Tasks

- [x] Remove critical booking store dependency on `service_type` input.
- [x] Enforce authoritative schema anchoring (`service_schema_id`) in validator/controller flow.
- [x] Remove `service_type` fallback logic from booking action/validator critical path.
- [x] Remove legacy Blade invoice fallback path from `BookingController`.
- [x] Retire `DocumentPayloadTransformer` file.
- [x] Update booking create UI payload path to canonical-only anchors.
- [x] Run syntax verification and finalize checklist.
