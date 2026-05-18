# Service Record Generalization Checklist

Last updated: 2026-05-15
Status: completed

## Goal

Generalize the runtime from `operations/service_instances/service_schemas` into the clearer and tighter model:

- `service_records`
- `service_record_rows`
- `schema_vectors`

The refactor also introduces stronger finance fields at row level and treats `service_group_key` as the record grouping context.

## Execution Checklist

- [x] Add control and tenant migrations for `service_schemas` -> `schema_vectors`, `operations` -> `service_records`, `service_instances` -> `service_record_rows`, and `operation_projections` -> `service_record_projections`.
- [x] Add tenant column migrations for `service_group_key`, `schema_vector_id`, `unit_name`, `base_cost`, `supplier_cost`, `discount_type`, `discount_value`, `discount_amount`, and `sell_price`.
- [x] Backfill renamed and added fields from current runtime data.
- [x] Introduce canonical models: `ServiceRecord`, `ServiceRecordRow`, `SchemaVector`, `ServiceRecordProjection`.
- [x] Keep temporary PHP compatibility wrappers for legacy model names where that reduces breakage during the transition.
- [x] Refactor creation/validation flow to canonical naming and schema vector resolution.
- [x] Update runtime finance calculations to support row-level discount, tax, supplier/base cost, and sell price.
- [x] Refactor document render payloads from `operation/services/service_instances` toward `service_record/service_rows/schema_vectors`, while preserving one-phase aliases.
- [x] Refactor extraction/materialization services and commands to `ServiceRecord` naming.
- [x] Replace tenant routes and controller flow with canonical `/service-records` endpoints.
- [x] Update tenant pages/components/labels from “Operations” to “Service Records”.
- [x] Update admin schema terminology from “service schemas” toward “schema vectors” where appropriate.
- [x] Update tests and builder preview payloads for the new canonical terms.
- [x] Run PHP syntax verification and frontend production build.
- [x] Move this checklist to completed and record the result in workflow history.

## Verification

- `php -l` passed for the changed service-record models, services, controllers, commands, and updated unit tests.
- `sail artisan test tests/Unit` passed: 12 tests, 71 assertions.
- `npx vite build --outDir "$(mktemp -d)"` passed.
- `sail artisan route:list | rg "service-records|operations|schemas|documents"` confirmed canonical `service-records.*` routes plus temporary `operations.*` compatibility redirects/wrappers.

## Notes

- Compatibility aliases remain intentionally present for transition safety:
  - PHP wrappers: `Operation`, `ServiceInstance`, `ServiceSchema`, `OperationProjection`
  - routes: `/operations...`
  - document aliases: `operation`, `services`, `service_instances`
- Raw unit compatibility was tightened by allowing legacy mass-assigned keys like `handler_key` and `service_schema_id` to map into the canonical service-record model fields.
