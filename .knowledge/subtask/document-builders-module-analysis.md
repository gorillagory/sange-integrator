# Document Builders Module Analysis

Date: 2026-05-13  
Scope: Current implementation analysis for tenant Document Builders (templates, bindings, preview/render pipeline, and access control).

## 1) Access + Routing Surface

Document Builder routes are tenant-scoped and protected by:
1. Domain: `{subdomain}.bayam.test`
2. Middleware: `web`, `auth`, `IdentifyTenant`
3. Module gate: `company_module:travel.documents`
4. Role gate: `super_admin_or_tenant_role:agency_admin,document_manager`

Source: `routes/web.php`

Implication:
- Access control design is correct for multi-tenant separation.
- Super admin has bypass via middleware logic, tenant roles constrained by active company context.

## 2) Data Model + Persistence

Model: `app/Models/DocumentTemplate.php`
- Fillable: `name`, `code`, `document_type`, `layout_vector`, `binding_index`
- Casts: `layout_vector` and `binding_index` to arrays
- `saving` hook:
  - normalizes `layout_vector` via `DocumentTemplateLayoutService`
  - regenerates `binding_index` via `DocumentTemplateBindingIndexService`

Migrations:
- `document_templates` base table: `tenant/shared/2026_04_29_184835_create_document_templates_table.php`
- `binding_index` added: `tenant/shared/2026_05_13_000100_add_binding_index_to_document_templates.php`

Implication:
- Binding index is deterministic and auto-maintained.
- Layout schema consistency is enforced at save-time, not only UI-time.

## 3) Builder Backend Flow

Controller: `app/Http/Controllers/Admin/DocumentTemplateController.php`

Core behavior:
1. `index()`
   - reads templates
   - computes/uses binding index
   - runs compatibility analysis against dictionary
2. `create()` / `edit()`
   - loads dictionaries by doc type
   - injects default layout vector
3. `store()` / `update()`
   - validates payload
   - normalizes layout through service
4. `preview()`
   - builds payload via `DocumentRenderContextFactory` + preview payload factory
   - compiles HTML via `PdfCompilerService`
   - streams PDF via DomPDF

Requests:
- `StoreDocumentTemplateRequest`
- `UpdateDocumentTemplateRequest`
- both enforce:
  - valid `document_type`
  - `layout_vector` array
  - deep validation through `DocumentTemplateLayoutService::validate()`

## 4) Layout/Binding Engine

Layout service: `DocumentTemplateLayoutService`
- Supported block types: `row`, `text`, `image`, `list`, `divider`, `spacer`, `table`, `page_break`
- Normalizes page + node shape
- Validates row/table/list invariants

Binding index service: `DocumentTemplateBindingIndexService`
- Extracts:
  - `data_paths`
  - `placeholder_paths` (`{{ path }}` in text blocks)
  - `list_paths`
  - `table_bindings`

Compatibility service: `DocumentTemplateCompatibilityService`
- Compares binding index against `DocumentVariableService` dictionary
- Produces `compatible` or `warning` status with structured issues

## 5) Frontend Builder Architecture

Entry page:
- `resources/js/Pages/Admin/Documents/Builder.vue`

Engine orchestration:
- `Composables/useBuilderPage.js`
- `Composables/document-builder/useDocumentEngine.js`
- includes:
  - selection model
  - drag/drop
  - undo/redo history
  - copy/cut/paste
  - preview payload injection

Key note:
- `builderPreviewData.js` currently uses static sample values for many fields.
- runtime PDF preview endpoint uses backend payload factories and may diverge from on-canvas preview in edge cases.

## 6) Rendering Pipeline

Compiler: `app/Services/PdfCompilerService.php`
- Normalizes layout input
- Compiles node tree to HTML
- Supports row grids, placeholder substitution, list/table loops, image rendering
- wraps output in master template with page CSS + optional watermark

Potential mismatch risk:
- UI preview binding (`useBlockBindings`) and backend compiler path resolution are separate implementations.
- If either changes independently, visual parity may drift.

## 7) Current Strengths

1. Solid tenant access boundaries via route middleware.
2. Deterministic layout normalization and binding extraction.
3. Compatibility checks surfaced in template index.
4. Unit tests exist for:
   - binding extraction
   - compatibility analysis

Sources:
- `tests/Unit/DocumentTemplateBindingIndexServiceTest.php`
- `tests/Unit/DocumentTemplateCompatibilityServiceTest.php`

## 8) Risks / Gaps

1. Preview parity risk:
   - frontend canvas preview payload + backend PDF preview payload are not fully unified.
2. Dictionary governance:
   - dictionary is static code-based; no tenant/runtime governance layer.
3. Builder complexity concentration:
   - mutation/drag/history logic is spread across many composables; onboarding/debug cost is high.
4. No explicit snapshot/version metadata in template rows beyond `version` field inside vector.

## 9) Recommended Next Actions

1. Unify preview contract:
   - create one canonical preview payload contract used by both canvas and PDF preview.
2. Add contract tests:
   - compare sample builder preview output vs compiler output for same layout + payload.
3. Add template lifecycle metadata:
   - status (`draft/active/deprecated`) + revision notes.
4. Add dictionary drift checks:
   - command/test to detect binding paths not covered by active dictionaries.

---

Conclusion:
The Document Builders module is structurally strong and already modularized. Main improvement area is parity hardening between frontend preview and backend rendering, plus governance around evolving document dictionaries and template lifecycle states.
