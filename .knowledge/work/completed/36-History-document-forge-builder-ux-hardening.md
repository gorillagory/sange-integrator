# Document Forge Builder UX Hardening Checklist

Last updated: 2026-05-16
Status: completed

## Goal

Fix the current document-builder pain points and make the editing flow more practical:
- broken image binding URLs
- missing save feedback
- tiring save flow
- weak mapping usability
- inspector scrolling friction
- preview mismatch between builder and printable output

## Checklist

- [x] Reassess the document builder end to end from canvas, inspector, data mapping, and preview output.
- [x] Fix broken image source resolution that produced `[object Object]` requests.
- [x] Preserve image dynamic-binding metadata during layout normalization.
- [x] Add visible save feedback inside the builder surface.
- [x] Add both `Save` and `Save & Exit` actions.
- [x] Auto-generate template code from the template name until manually edited.
- [x] Keep smart mapping visible while editing instead of hiding it behind a tab.
- [x] Improve copy-key behavior with a working fallback and toast feedback.
- [x] Rework preview mode so it uses compiled HTML instead of editable canvas chrome.
- [x] Add page-size support improvements for the printable sheet workflow.
- [x] Verify backend syntax and frontend production build.

## Outcome

- Dynamic image bindings no longer collapse into broken `/[object Object]` URLs.
- Image nodes now retain `source_mode` and `data_key` across save/load cycles.
- The builder page now shows success/error toast feedback instead of silently saving.
- Users now have:
  - `Save`
  - `Save & Exit`
  - `Open PDF Preview`
  - `Print Preview`
- Template code now follows the template name automatically until the user overrides it.
- Smart mapping is now always visible under the main tool panel, so keys stay accessible while editing blocks or page settings.
- Copy-key behavior now has:
  - clipboard API support
  - fallback copy support
  - toast confirmation
- Print preview now compiles server-side HTML and displays it inside a paper-like screen sheet instead of reusing the editable block canvas.
- The live design canvas still exists for structural editing, but preview mode now reflects the printable contract much more closely.
- A5 was added to the page-size workflow alongside A4, Letter, and Legal.

## Verification

- `php -l app/Http/Controllers/Admin/DocumentTemplateController.php`
- `php -l app/Services/DocumentTemplateLayoutService.php`
- `php -l app/Services/PdfCompilerService.php`
- `php -l app/Http/Requests/Admin/StoreDocumentTemplateRequest.php`
- `php -l app/Http/Requests/Admin/UpdateDocumentTemplateRequest.php`
- `npx vite build --outDir "$(mktemp -d)"`
