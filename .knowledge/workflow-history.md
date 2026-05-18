# Workflow History Map

Last updated: 2026-05-18

## Purpose

Track execution history from completed history artifacts under `.knowledge/work/completed`, ordered oldest (`01`) to latest.

## Timeline

1. `01-History-module-transition.md` (2026-05-12)
   - Established work queue structure.
   - Refined transition draft with authoritative rules.

2. `02-History-module-transition-phase1-2.md` (2026-05-12)
   - Implemented canonical schema identity/lifecycle foundation.
   - Added booking anchoring/snapshot columns and dual-write compatibility paths.

3. `03-History-module-transition-phase3.md` (2026-05-12)
   - Introduced canonical render context service.
   - Routed preview and forward invoice generation into unified template compiler path.

4. `04-History-module-transition-phase4.md` (2026-05-12)
   - Added schema-driven Laravel-native validation compiler.
   - Enforced governed `service_details` validation via schema anchors.

5. `05-History-module-transition-phase5.md` (2026-05-12)
   - Added template `binding_index` derivation.
   - Added compatibility analysis and surfaced issues in document index.

6. `06-History-module-transition-phase6.md` (2026-05-12)
   - Removed critical-path legacy `service_type` input dependency.
   - Removed legacy invoice Blade fallback and retired `DocumentPayloadTransformer`.

7. `07-History-module-transition-closeout.md` (2026-05-13)
   - Performed closeout verification in Sail runtime.
   - Targeted unit tests passed.
   - Documented tenant migration runtime blocker (`tenant DB config`).

8. `08-History-workflow-sop-standardization.md` (2026-05-13)
   - Documented the mandatory workflow SOP for User <-> Codex execution.
   - Added the workflow history map and tightened `.knowledge` process references around it.
   - Updated the knowledge base indexes so SOP + history are part of the standard operating memory.

9. `09-History-audit-trail-logging.md` (2026-05-13)
   - Hardened central `AuditEngine` with request metadata and fallback logging path.
   - Added auth/access/user-admin event coverage.
   - Enabled `Auditable` on control-side core models.
   - Added unit tests for signature + fallback behavior.
   - Documented audit module architecture and known hardening queue.

10. `10-History-control-db-users-table-fix.md` (2026-05-13)
   - Resolved runtime login failure from missing `users` table on `control` DB.
   - Executed control migrations and control seeders in correct order.
   - Executed tenant migrations and tenant template seed baseline.
   - Verified `sys.bayam.test:8000/login` returns HTTP 200.

11. `11-History-enrollment-refresh-fix.md` (2026-05-13)
   - Fixed enrollment policy to allow system-level admins without tenant memberships.
   - Relaxed email validation from DNS-enforced to standard email validation.
   - Changed user list ordering to newest-first for immediate visibility of newly created users.
   - Added explicit frontend reload on successful user/company/group mutations.
   - Added backend company search filtering to match existing UI search behavior.

12. `12-History-bayam-travel-role-users-seeder.md` (2026-05-13)
   - Added `BayamTravelRoleUsersSeeder` for one user per role with shared password.
   - Wired seeder into `DatabaseSeeder`.
   - Seeded control DB and verified row growth (`users`, `model_has_roles`, `company_user`).

13. `13-History-audit-trail-segment-draft.md` (2026-05-13)
   - Drafted simple audit-trail finalization design for `segment` field.
   - Defined data model change, runtime mapping, rollout, and acceptance criteria.
   - Stored draft in `.knowledge/subtask`.

14. `14-History-audit-log-visualization.md` (2026-05-13)
   - Added system audit log access route and controller.
   - Added filterable, paginated audit event table.
   - Added simple visual analytics (metrics, segment cards, trend bars, top actions).
   - Added navigation entry in system workspace shell.

15. `15-History-rbac-sidebar-enforcement.md` (2026-05-13)
   - Enforced sidebar RBAC visibility through shared `auth.rbac` context for system and tenant layouts.
   - Aligned system route-level authorization so `companies` and `users` endpoints are super-admin only.
   - Closed UI/backend RBAC mismatch where hidden sidebar entries were still directly accessible by URL.

16. `16-History-login-branding-subdomain-identity.md` (2026-05-13)
   - Added shared host-based branding context for login rendering (`brand` Inertia prop).
   - Redesigned login UI to show tenant identity (logo, theme color, subdomain) when accessed on tenant host.
   - Added dynamic `<title>` and favicon on login page using tenant logo with system fallback.

17. `17-History-login-subdomain-brand-detection-fix.md` (2026-05-13)
   - Fixed tenant login brand detection for hosts carrying explicit ports (e.g., `bt.bayam.test:8000`).
   - Normalized host parsing and hardened subdomain lookup with case/whitespace-tolerant matching.
   - Removed false system fallback for valid tenant subdomain login requests.

18. `18-History-rbac-superadmin-sidebar-regression-fix.md` (2026-05-13)
   - Resolved super-admin sidebar regression caused by strict/global RBAC lookup assumptions.
   - Made role scope reads tolerant to both `company_id = 0` and `company_id IS NULL`.
   - Removed hardcoded model type dependency by resolving against runtime morph class + fallback.

19. `19-History-sidebar-empty-state-rbac-fallback.md` (2026-05-13)
   - Added UI-level defensive fallback in system and tenant layouts when RBAC payload resolves to empty/all-false for authenticated users.
   - Prevented complete sidebar blank state while preserving backend middleware as access control source of truth.
   - Restored navigation visibility to keep workspace operable during RBAC payload drift/debug cycles.

20. `20-History-sidebar-missing-runtime-diagnosis.md` (2026-05-13)
   - Verified control DB role data is present for super admin and not blocked by missing permission seeds.
   - Cleared Laravel/runtime permission caches in container.
   - Rebuilt frontend assets and removed stale `public/hot` marker to avoid stale Vite runtime asset loading.

21. `21-History-vite-manifest-login-mismatch-fix.md` (2026-05-13)
   - Resolved login crash from Vite manifest mismatch in `app.blade.php`.
   - Removed direct page component entry from `@vite(...)`, leaving only build entrypoints.
   - Cleared compiled views and framework caches to apply template/runtime changes.

22. `22-History-inertia-shared-props-middleware-registration-fix.md` (2026-05-13)
   - Diagnosed missing sidebar/user payload to unregistered Inertia shared-props middleware.
   - Registered `HandleInertiaRequests` in web middleware pipeline in `bootstrap/app.php`.
   - Restored shared `auth/rbac/brand` prop delivery by re-enabling middleware execution path.

23. `23-History-document-builders-analysis-dump.md` (2026-05-13)
   - Completed focused analysis of Document Builders module (access, model, builder engine, render pipeline).
   - Documented findings, risks, and recommended next actions in `.knowledge/subtask/document-builders-module-analysis.md`.
   - Captured module-state snapshot for discrepancy review and iteration planning.

24. `24-History-environment-docs-normalization.md` (2026-05-14)
   - Normalized local environment defaults around the control DB (`sange_control`) and Sail-published ports.
   - Removed conflicting `.env.example` port/database defaults.
   - Replaced the default Laravel root README with project-specific setup and architecture guidance.
   - Aligned devcontainer forwarded ports with the documented local runtime baseline.

25. `25-History-booking-module-completion.md` (2026-05-15)
   - Hardened booking creation and invoice locking so contract selection must match the active tenant company and chosen client.
   - Added structured passenger roster capture to booking creation and surfaced stored passenger data on booking review.
   - Restored the missing tenant reports page with booking metrics, revenue snapshots, and recent activity.
   - Verified PHP syntax and successful frontend compilation via temporary Vite output directory after existing `public/build` permission blockage.

26. `26-History-knowledge-simplification.md` (2026-05-15)
   - Reduced the active root knowledge set and moved superseded reference docs into `.knowledge/archive/`.
   - Updated core architecture, data model, and development rules around the dynamic operations/vector-first direction.
   - Preserved historical execution and design artifacts under `work/completed/`, `subtask/`, and `archive/`.

27. `27-History-dynamic-operations-generalization.md` (2026-05-15)
   - Renamed the active runtime toward `operations` and `service_instances` with a tenant migration and handler-key backfill.
   - Replaced the live booking flow with canonical operations routes, pages, controller logic, and service-instance-only payload capture.
   - Added canonical document render roots plus `booking.*` compatibility aliases and shipped dry-run analytics extraction scaffolding.
   - Verified PHP syntax and temporary-output Vite production build; documented local PHPUnit blockage from PHP `8.2.5` vs required `>= 8.4.0`.

28. `28-History-architecture-reassessment-report.md` (2026-05-15)
   - Reassessed the post-generalization runtime from an architect perspective.
   - Added an explicit architecture assessment report for the current dynamic operations model.
   - Added service handling flow and mind-map artifacts to make capture, document, and extraction paths easier to study.

29. `29-History-module-verification-sweep.md` (2026-05-15)
   - Verified enabled tenant modules against static PHP syntax and full frontend production compilation.
   - Confirmed the operations, documents, and schemas surfaces all compile together after the recent generalization work.
   - Documented the remaining framework-test blockers: local PHP `8.2.5` vs required `>= 8.4.0`, plus Sail unavailable because Docker/Podman is not running.

30. `30-History-strict-architect-package.md` (2026-05-15)
   - Converted the runtime assessment into a stricter architect package with target-state architecture, transition roadmap, booking compatibility deprecation plan, and handler contract specification.
   - Added explicit source-of-truth decisions, phased acceptance criteria, and compatibility removal conditions.
   - Expanded the active knowledge index so the new architect package is part of the standard project memory.

31. `31-History-operations-roadmap-implementation.md` (2026-05-15)
   - Executed the architect roadmap in code by consolidating runtime truth onto `service_instances`, removing booking-era wrappers and aliases, and formalizing handler registration.
   - Added canonical document-number access, handler-aware extraction, and the first materialized operation projection path for downstream analytics.
   - Verified PHP syntax and frontend compilation as far as the local environment allows, while retaining physical legacy storage only as deferred compatibility debt.

32. `32-History-service-record-generalization.md` (2026-05-15)
   - Renamed the active runtime again into the tighter `service_records`, `service_record_rows`, and `schema_vectors` vocabulary, including control and tenant migrations plus projection-table generalization.
   - Reworked runtime creation, document payloads, extraction/materialization services, tenant routes, and tenant UI around `ServiceRecord` and schema-vector row finance truth while preserving one-phase `operations` and `service_instances` aliases.
   - Hardened transition compatibility by accepting legacy mass-assigned keys (`handler_key`, `service_schema_id`, `operation_id`), fixing detached unit payload generation, and validating the refactor with a green Sail unit suite plus production Vite build.

33. `33-History-blueprint-forge-tenant-schema-frontend-simplification.md` (2026-05-15)
   - Simplified the schema governance frontend by making Blueprint Forge the explicit central authoring surface and reducing tenant schema pages into lightweight review screens.
   - Removed tenant-facing cues that suggested a second full schema editor, replacing them with review, preview, and governance guidance.
   - Verified the UI simplification pass with a clean Vite production build and documented the remaining deferred need for company-scoped activation controls.

34. `34-History-blueprint-forge-builder-restoration.md` (2026-05-15)
   - Restored the richer original schema-vector builder infrastructure into Blueprint Forge using the advanced field editor, compiled JSON preview, pricing units, and document output designer.
   - Reintroduced automatic central vector-key generation so the forge no longer depends on manual database-key entry for every new blueprint.
   - Kept the tenant schema side lightweight while re-strengthening the central authoring surface, then verified the frontend with a clean Vite production build.

35. `35-History-blueprint-forge-ux-clarity-adjustment.md` (2026-05-15)
   - Corrected Blueprint Forge UX drift caused by tenant-light builder cards being reused inside the dark system workspace.
   - Made the shared schema-builder components theme-aware for system use and tightened labels so the page scans more clearly as an operational control-plane tool.
   - Verified the adjusted UX pass with another clean Vite production build.

36. `36-History-document-forge-builder-ux-hardening.md` (2026-05-16)
   - Fixed document-builder pain points across image bindings, save feedback, smart mapping visibility, and printable preview flow.
   - Added `Save` plus `Save & Exit`, preserved template-code auto-generation, and hardened copy-key behavior with fallback feedback.
   - Verified backend syntax and a production Vite build after the UX hardening pass.

## Current State Summary

Completed:
1. Workflow standard is established and used.
2. Module transition phases 1-6 implemented.
3. Closeout verification executed with documented outcomes.
4. Audit trail baseline is now active across auth/access/control-model flows.
5. Control + tenant baseline migrations/seeding have been re-aligned after runtime schema drift.
6. Enrollment and roster refresh UX now has deterministic post-save list refresh.
7. Bayam Travel role-based test identities are now reproducible via seeding.
8. Audit segment finalization plan is documented and ready for implementation.
9. System-side audit log access and visualization is now available.
10. System and tenant sidebar visibility is now role-aware, with system route enforcement aligned for restricted modules.
11. Login experience now adapts to system vs tenant context, reducing sign-in domain confusion.
12. Subdomain login branding now resolves correctly on port-based local domains.
13. Super-admin role visibility in system sidebar is now resilient to legacy role-row shape differences.
14. Layout navigation now avoids total blank state when RBAC payload unexpectedly resolves empty.
15. Runtime now serves rebuilt assets without stale hot-reload marker interference.
16. Inertia page rendering no longer depends on non-entry page files in Vite manifest.
17. Shared Inertia props are now restored through explicit web middleware registration.
18. Document Builders module technical snapshot is now documented in subtask knowledge artifacts.
19. Local setup docs and defaults now align with the multi-tenant control DB and Sail runtime ports.
20. Booking runtime now captures passenger roster input, enforces contract/client coherence, and exposes a working tenant reports view.
21. Active knowledge docs are now smaller and centered on the dynamic operations platform direction, while older snapshots remain preserved in archive/history areas.
22. The tenant travel runtime now operates through canonical `operations` and `service_instances`, with passenger capture removed from new operational writes.
23. Architect-facing runtime assessment and service handling diagrams are now part of the active knowledge set.
24. A stricter architecture package now defines the intended target state, transition sequencing, handler contract, and booking compatibility retirement path.
25. The architect roadmap is now implemented in the application layer, and the live naming has been tightened further into `service_records`, `service_record_rows`, and `schema_vectors` with temporary operation-era aliases still present only for transition safety.
26. Schema governance UX is now simpler: central Blueprint Forge is the single authoring story, while tenant schema pages are positioned as review surfaces until company-scoped activation exists.
27. Blueprint Forge has been re-strengthened with the original rich builder infrastructure so simplification on the tenant side does not weaken central authoring safety or practicality.
28. Blueprint Forge now also reads more clearly at the UI level, with system-consistent dark surfaces and more concise builder labels.
29. Document Forge now has a harder-working builder flow: image bindings survive normalization, save feedback is visible, smart mapping stays on screen, and print preview uses compiled HTML rather than editable canvas chrome.

Pending environment action:
1. Complete an application smoke pass through the browser/runtime flows on the new `/service-records` tenant surface and the revised schema-governance screens.
2. Decide whether to add a company-scoped activation table before re-introducing tenant-side schema controls beyond review and preview.
3. Run a browser smoke pass specifically through the revised tenant document builder to validate save toasts, print preview fidelity, always-visible smart mapping, and image bindings against real templates.

## Source of Truth

Primary trace artifacts:
1. `.knowledge/work/completed/NN-History-*.md`
2. `.knowledge/subtask/solution-draft.md` execution status section
