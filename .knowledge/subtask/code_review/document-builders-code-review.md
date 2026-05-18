# Document Builders Code Review

Date: 2026-05-13  
Scope: Document template builder, binding/index compatibility, preview/render pipeline, and alignment with `.knowledge` module objectives.

## Objective Alignment Snapshot

The current code is broadly aligned with the `.knowledge` objectives for:

1. Tenant ownership of document templates.
2. Canonical nested dot-path render bindings.
3. Derived `binding_index` generation on template save.
4. Compatibility analysis against document dictionaries.
5. Forward PDF generation through `DocumentRenderContextFactory` + `PdfCompilerService`.

The booking/schema side has also advanced beyond older notes:

1. `service_code`, `service_name`, `version`, `status`, and `is_default` exist on `ServiceSchema`.
2. Booking services now persist schema anchors and snapshots through `CreateBookingAction`.
3. Governed booking payload validation is handled by `BookingServicePayloadValidator`.

Remaining review focus is therefore the document builder/runtime contract, not the already-addressed schema identity work.

## Findings

### 1. Dynamic image bindings are stripped during backend normalization

Severity: High  
Files:
- `resources/js/Pages/Admin/Documents/Composables/document-builder/layoutHelpers.js:83`
- `app/Services/DocumentTemplateLayoutService.php:136`
- `app/Services/PdfCompilerService.php:169`

The frontend creates image blocks with `source_mode` and `data_key`, and the PDF compiler knows how to resolve dynamic images when those keys are present. The backend normalizer does not preserve either property for `image` nodes; it only keeps `label`, `url`, and `asset_path`.

Impact:
- A template author can configure an image block to bind to `company.logo_url` or `main_group.logo_url`.
- Save/reload normalization drops the dynamic binding metadata.
- PDF generation silently falls back to static image fields.

Objective gap:
- This breaks the target objective that preview and generation use one canonical nested-key render context.

Recommended fix:
- Preserve `source_mode` and `data_key` for image nodes in `DocumentTemplateLayoutService::normalizeNode()`.
- Add a unit test that normalizes an image node with `source_mode=dynamic` and confirms the compiler resolves the bound payload value.

### 2. Canvas preview does not resolve text placeholders the same way as PDF output

Severity: High  
Files:
- `resources/js/Pages/Admin/Documents/Composables/document-builder/useBlockBindings.js:17`
- `app/Services/PdfCompilerService.php:150`

The PDF compiler replaces `{{ nested.path }}` placeholders inside text content. The frontend builder preview only resolves `node.data_key`; when a text block contains placeholders in `content`, it returns the raw content string.

Impact:
- The on-canvas preview can show `{{ invoice.number }}` while the generated PDF shows the actual invoice number.
- Authors cannot reliably validate output before opening the PDF preview.

Objective gap:
- This is direct preview/render parity drift, which `.knowledge/subtask/solution-draft.md` identifies as a Phase 3 acceptance concern.

Recommended fix:
- Move placeholder replacement into a shared frontend helper matching the backend regex and path resolution behavior.
- Add a small frontend/unit contract test or snapshot test using the same payload shape as `DocumentPreviewPayloadFactory`.

### 3. Layout validation normalizes before validating, so malformed blocks can be silently dropped

Severity: Medium  
File:
- `app/Services/DocumentTemplateLayoutService.php:63`

`validate()` calls `normalize()` before checking the layout. `normalize()` discards unsupported block types and non-array nodes before validation runs.

Impact:
- Invalid submitted layout structures can become "valid" because bad nodes are removed first.
- This creates silent data loss and makes form errors less useful.
- It weakens the stated save-time contract that unsupported block types and malformed structures are rejected.

Objective gap:
- The `.knowledge/archive/design/module-building-document.md` contract says validation is enforced on save. Current behavior enforces a normalized subset, not the submitted contract.

Recommended fix:
- Split validation into two passes:
  1. Validate the submitted raw structure.
  2. Normalize only after validation passes.
- Keep a repair/legacy-normalize path only for loading old saved templates, not for accepting new invalid submissions.

### 4. Schema-captured service fields are available in render payloads but not discoverable in the dictionary

Severity: Medium  
Files:
- `app/Services/DocumentRenderContextFactory.php:48`
- `app/Services/DocumentVariableService.php:244`
- `app/Services/DocumentTemplateCompatibilityService.php:118`

Runtime render payloads include each booking service's governed details under `booking.services[].fields`. Compatibility logic explicitly allows `service.fields.*`, but the dictionary shown to authors only documents `service.date`, `service.time`, `service.title`, `service.details`, and `service.confirmation`.

Impact:
- The rendering pipeline has the data needed for schema-aware service fields.
- Template authors are not guided to bind those fields.
- Compatibility can allow paths that the builder dictionary does not expose.

Objective gap:
- This leaves the formal schema-to-render binding contract incomplete. It is the remaining bridge between service schema vectors and document vectors.

Recommended fix:
- Extend the dictionary model to expose governed schema fields for active service schemas, likely grouped under a service-field namespace.
- Decide and document whether table rows should use `fields.flight_number`, `service.fields.flight_number`, or another canonical row-relative path.
- Add compatibility coverage for the chosen namespace.

### 5. Official invoice rendering still hydrates mutable booking state instead of issuing immutable document snapshots

Severity: Medium  
Files:
- `app/Http/Controllers/BookingController.php:146`
- `app/Services/DocumentRenderContextFactory.php:16`
- `app/Actions/Travel/CreateBookingAction.php:66`

Booking creation persists `payload_snapshot` on each booking service, which is good. Invoice download, however, still rehydrates the current booking, client, contract, company, passengers, and service rows through `DocumentRenderContextFactory::makeInvoiceFromBooking()`.

Impact:
- Re-downloading an official invoice after later client/company/contract/template changes may produce different output.
- This falls short of the replay-safe document objective in `.knowledge/subtask/solution-draft.md`.

Objective gap:
- The target done criteria require official document replay to use frozen payload snapshots, not mutable runtime state.

Recommended fix:
- Introduce an issued-document snapshot table or append-only JSON snapshot for each official invoice issuance.
- On first invoice generation, persist the resolved render payload, template id/version/checksum, and issued metadata.
- On re-download, render from the stored issuance snapshot unless an explicit reissue flow creates a new snapshot.

## Positive Notes

1. `DocumentTemplate` centralizes layout normalization and binding extraction in the model save hook.
2. `DocumentTemplateBindingIndexService` and `DocumentTemplateCompatibilityService` are cleanly separated and already covered by unit tests.
3. The document index now counts `header/body/footer` nodes recursively, which resolves an older UI drift item.
4. `BookingServicePayloadValidator` closes the earlier backend validation gap for governed booking fields.
5. The tenant/control boundary remains clear: schemas are control-side, templates and booking data are tenant-side.

## Recommended Execution Order

1. Fix image normalization metadata loss.
2. Align frontend text placeholder rendering with `PdfCompilerService`.
3. Split raw layout validation from normalization.
4. Formalize schema-field dictionary exposure for `booking.services[].fields`.
5. Add official document issuance snapshots for replay stability.

## Suggested Test Additions

1. `DocumentTemplateLayoutServiceTest`
   - preserves image `source_mode` and `data_key`
   - rejects unsupported raw block types instead of dropping them
2. `PdfCompilerServiceTest`
   - resolves text placeholders
   - resolves dynamic image source from payload
3. Frontend builder binding test
   - resolves `{{ invoice.number }}` in canvas preview with the same output as PDF compiler expectations
4. Document issuance regression test
   - generated invoice payload remains stable after related booking/client/company data changes

## Conclusion

The document builders are structurally close to the stated architecture, and the newer schema/booking work has closed several earlier gaps. The main remaining risk is contract drift: frontend preview, backend normalization, dictionary governance, and official document replay are not yet fully locked to one durable render contract.
