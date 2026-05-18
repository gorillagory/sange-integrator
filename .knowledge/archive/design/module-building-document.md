# Module Building Document: Document Builders & Schema Vectors

Last updated: 2026-05-12

## 1) Scope

This document covers the end-to-end module composed of:

1. Service Schema Vector Builder (`Admin/Schemas`) for defining dynamic service payload structures.
2. Document Template Builder (`Admin/Documents`) for defining printable PDF layout vectors.
3. Booking runtime integration (`Bookings/Create` + `CreateBookingAction`) where schema vectors drive input capture.
4. PDF compile/render services for document preview and generation infrastructure.

This is focused on structure and contracts, not UI cosmetics.

## 2) Multi-tenant Boundary (Critical)

### Control database (shared/global)
- `service_schemas` table (model: `App\Models\ServiceSchema`, `$connection = 'control'`)
- Stores schema vectors used by tenants in the same industry.

### Tenant database (per company)
- `document_templates` table (model: `App\Models\DocumentTemplate`)
- `bookings`, `booking_services`, `passengers` tables (travel runtime payloads)

### Effective boundary
- Schema definitions are control-scoped and filtered by tenant industry.
- Booking instances and document templates are tenant-scoped operational records.

## 3) Route and Access Surface

Defined in `routes/web.php` under tenant domain `{subdomain}.bayam.test` with `IdentifyTenant`.

### Schema vectors
- `GET /admin/schemas` -> `SchemaController@index`
- `GET /admin/schemas/create` -> `SchemaController@create`
- `POST /admin/schemas` -> `SchemaController@store`
- `GET /admin/schemas/{id}/edit` -> `SchemaController@edit`
- `PUT /admin/schemas/{id}` -> `SchemaController@update`
- `DELETE /admin/schemas/{id}` -> `SchemaController@destroy`
- Middleware: `company_module:travel.schemas` + `super_admin_or_tenant_role:agency_admin`

### Document templates
- `GET /admin/documents` -> `DocumentTemplateController@index`
- `GET /admin/documents/create` -> `DocumentTemplateController@create`
- `POST /admin/documents` -> `DocumentTemplateController@store`
- `GET /admin/documents/{id}/edit` -> `DocumentTemplateController@edit`
- `PUT /admin/documents/{id}` -> `DocumentTemplateController@update`
- `DELETE /admin/documents/{id}` -> `DocumentTemplateController@destroy`
- `GET /admin/documents/{subdomain}/{id}/preview` -> `DocumentTemplateController@preview`
- Middleware: `company_module:travel.documents` + `super_admin_or_tenant_role:agency_admin,document_manager`

## 4) Service Schema Vector Contract

Canonical stored contract in `service_schemas.schema_payload`:

```json
{
  "fields": [
    {
      "key": "passport_number",
      "type": "string|number|float|email|date|datetime|time|file",
      "label": "Passport Number",
      "ui_component": "text_input|textarea|date|datetime|time|file|typeahead",
      "grid_span": 1,
      "rules": ["required"],
      "is_array": false,
      "order": 0,
      "placeholder": "optional",
      "text_transform": "none|uppercase|lowercase|capitalize",
      "data_source": {
        "endpoint": "/api/...",
        "cascade_from": "parent_key"
      },
      "file_options": {
        "max_size_mb": 5,
        "max_count": 1,
        "allowed_types": "*",
        "enable_preview": false
      }
    }
  ],
  "document_output": "<html with {{ key }} pills>",
  "pricing_units": ["pax", "room"]
}
```

Compile source: `resources/js/Pages/Admin/Schemas/Composables/useSchemaCompiler.js`.

## 5) Document Layout Vector Contract

Canonical normalized contract (backend + frontend aligned):

```json
{
  "version": 1,
  "page": {
    "isPage": true,
    "size": "A4",
    "orientation": "portrait|landscape",
    "margins": "10mm",
    "backgroundColor": "#ffffff",
    "watermarkText": null,
    "watermarkOpacity": 0.1,
    "watermarkColor": "#e5e7eb"
  },
  "header": [],
  "body": [],
  "footer": []
}
```

Supported block types (`DocumentTemplateLayoutService` + frontend `layoutHelpers`):
- `row`
- `text`
- `image`
- `list`
- `divider`
- `spacer`
- `table`
- `page_break`

Validation rules enforced on save:
- Zone arrays must be valid arrays.
- `row` must have columns and block arrays.
- `list` requires `data_key`.
- `table` requires `data_key` and at least one column.

## 6) Runtime Data Flow

### A. Schema Builder save flow
1. User edits fields in `Admin/Schemas/Builder.vue`.
2. `useSchemaCompiler` compiles UI state -> strict JSON payload.
3. `SchemaController@store/update` validates and persists to `control.service_schemas`.
4. Industry lock enforced via `Rule::in([$company->industry])`.

### B. Booking creation flow (where schema vectors are consumed)
1. `BookingController@create` loads schemas by tenant industry.
2. `Bookings/Create.vue` -> `addService()` reads selected schema and builds `service_details` object from `schema_payload.fields`.
3. Dynamic field rendering in `Bookings/Components/ServiceRow.vue`.
4. Submit -> `CreateBookingAction` stores:
   - `booking_services.service_schema_id`
   - `booking_services.service_type`
   - `booking_services.service_details`
   - `booking_services.payload` (duplicate of details)

### C. Document Builder save/preview flow
1. User edits layout vector in `Admin/Documents/Builder.vue`.
2. `useBuilderPage` saves `layout_vector` to `/admin/documents`.
3. `DocumentTemplate` model normalizes layout in `saving` hook.
4. Preview endpoint uses:
   - `DocumentPreviewPayloadFactory` (mock payload by `document_type`)
   - `PdfCompilerService` (vector -> HTML)
   - DomPDF stream output.

## 7) Key Components and Ownership

### Backend
- `app/Http/Controllers/Admin/SchemaController.php`  
  Control-plane CRUD for schema vectors.
- `app/Http/Controllers/Admin/DocumentTemplateController.php`  
  Tenant document template CRUD + preview.
- `app/Services/DocumentTemplateLayoutService.php`  
  Layout normalization + validation contract.
- `app/Services/PdfCompilerService.php`  
  Compiles vector blocks into printable HTML.
- `app/Services/DocumentVariableService.php`  
  Dictionary keys for template binding UX.
- `app/Services/DocumentPreviewPayloadFactory.php`  
  Preview payload provider by doc type.

### Frontend
- `resources/js/Pages/Admin/Schemas/*`  
  Schema vector authoring, compile preview JSON, rich `document_output`.
- `resources/js/Pages/Admin/Documents/*`  
  Layout vector visual builder, DnD, history, preview mode.
- `resources/js/Pages/Bookings/Create.vue`
- `resources/js/Pages/Bookings/Components/ServiceRow.vue`  
  Runtime dynamic form generated from schema fields.

## 8) Current Discrepancies / Risks

1. `service_schemas.service_type` is globally unique at DB level (`unique()`), while controller uniqueness is scoped by industry.  
   Impact: same `service_type` across industries is blocked unexpectedly.

2. `DocumentPayloadTransformer` appears orphaned / not wired to active invoice download flow.  
   `BookingController@downloadInvoice` still renders `pdf.invoice` view directly.

3. `DocumentPayloadTransformer::transform()` uses flat keys (`company_name`) but dictionary + compiler patterns use nested keys (`company.name`).  
   Impact: if this transformer is reintroduced, bindings may fail.

4. `DocumentPayloadTransformer::compileServiceDescription()` uses `json_parse(...)` (non-standard helper) and may fail unless custom macro exists.

5. `Admin/Documents/Index.vue` block counter still reads `parsed.nodes?.length`, but canonical vector uses `header/body/footer` arrays.  
   Impact: displayed node count is incorrect.

6. Schema `document_output` is captured in schema builder and stored in payload, but no active booking->document generation path currently consumes it end-to-end.

7. Booking `service_details` validation is structural only (`array`) and not schema-driven at backend.  
   Impact: clients can submit unexpected keys/types unless strict server-side schema validation is added.

## 9) Safe Extension Rules

1. Keep control/tenant separation strict:
   - No tenant operational writes to control DB except schema management.
   - No global schema mutations from booking runtime.

2. Version layout vector deliberately:
   - Increment `layout_vector.version` when breaking format changes occur.
   - Keep normalization backward-compatible.

3. For schema vector changes:
   - Preserve `fields[]` backward compatibility where possible.
   - Add migration strategy before renaming/removing keys used in saved bookings.

4. For document compilation:
   - Add block types only when both frontend builder and backend compiler support them.
   - Update `DocumentTemplateLayoutService` validation and normalization first.

5. If enabling schema-driven server validation:
   - Load schema by `service_schema_id/service_type`.
   - Validate each submitted `service_details` key against `schema_payload.fields`.
   - Enforce type/rules at backend before persisting.

## 10) Quick Verification Checklist

1. Create schema vector in `/admin/schemas/create` with required fields and `is_array` samples.
2. Use schema in `/bookings/create`; verify dynamic fields map to `service_details`.
3. Save document template in `/admin/documents/create`; include `table` and `list` data bindings.
4. Trigger document preview and ensure values resolve from payload keys.
5. Confirm persisted records:
   - `control.service_schemas.schema_payload`
   - `tenant.booking_services.service_details/payload`
   - `tenant.document_templates.layout_vector`

## 11) Comparison Note (Current vs Proposal)

Related proposal document:
- `.knowledge/subtask/respond-module-building-document.md`

Interpretation rule:
- This file (`module-building-document.md`) describes the verified **current implementation state**.
- The response document describes a **target-state architecture proposal**.

Major deltas between current and target:
1. Identity model:
   - Current: `service_type`
   - Target: `service_code` + `service_name` (+ existing relational id)
2. Schema lifecycle:
   - Current: no explicit persisted schema version lifecycle model
   - Target: versioned lifecycle (`draft`, `active`, `deprecated`, `archived`)
3. Validation:
   - Current: structural booking payload validation
   - Target: schema-aware field/type/rule enforcement at backend

Execution plan for these deltas is tracked in:
- `.knowledge/subtask/solution-draft.md`
