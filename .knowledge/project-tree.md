# Project Tree (Curated)

This is a focused tree of active product areas (excluding `vendor`, `node_modules`, and other generated/runtime directories).

```text
sange-integrator/
|- app/
|  |- Console/Commands/
|  |  |- ImportDocumentTemplateRow.php
|  |  |- SeedTenantDocumentTemplate.php
|  |  `- TenantMigrationManager.php
|  |- Http/
|  |  |- Controllers/
|  |  |  |- Admin/
|  |  |  |  |- DocumentTemplateController.php
|  |  |  |  `- SchemaController.php
|  |  |  |- Auth/
|  |  |  |- System/
|  |  |  |  |- BlueprintController.php
|  |  |  |  |- CompanyController.php
|  |  |  |  `- UserController.php
|  |  |  |- BookingController.php
|  |  |  |- ClientController.php
|  |  |  |- Controller.php
|  |  |  |- ContractController.php
|  |  |  |- ProfileController.php
|  |  |  `- ReportController.php
|  |  |- Middleware/
|  |  |  |- AllowSuperAdminOrTenantRole.php
|  |  |  |- EnsureCompanyModuleEnabled.php
|  |  |  |- HandleInertiaRequests.php
|  |  |  |- IdentifyTenant.php
|  |  |  `- ResetPermissionTeam.php
|  |  `- Requests/
|  |     |- Admin/
|  |     |- Auth/
|  |     `- System/
|  |- Models/
|  |  |- Booking.php
|  |  |- Client.php
|  |  |- Company.php
|  |  |- Contract.php
|  |  |- DocumentTemplate.php
|  |  |- Module.php
|  |  |- ServiceSchema.php
|  |  `- User.php
|  |- Providers/AppServiceProvider.php
|  |- Services/
|  |  |- AuthRedirectService.php
|  |  |- DocumentTemplateLayoutService.php
|  |  |- PdfCompilerService.php
|  |  `- TenantProvisioningService.php
|  `- Traits/Auditable.php
|- bootstrap/
|  |- app.php
|  `- providers.php
|- config/
|  |- database.php
|  |- permission.php
|  `- (standard Laravel config set + session/auth updates)
|- database/
|  |- migrations/
|  |  |- control/
|  |  |  |- users/cache/jobs base tables
|  |  |  |- companies + company_user
|  |  |  |- modules + company_module
|  |  |  |- service_schemas + global_clients + flights
|  |  |  `- audit + main_group expansions
|  |  `- tenant/
|  |     |- shared/ (contracts + document templates)
|  |     `- travel/ (bookings + passengers + services + invoice templates)
|  |- seeders/
|  |  |- CompanyModuleSeeder.php
|  |  |- ModuleSeeder.php
|  |  |- RbacSeeder.php
|  |  `- SchemaSeeder.php
|  `- data/document_templates/
|- docker/
|  `- pgsql/create-databases.sql
|- resources/
|  |- css/app.css
|  |- js/
|  |  |- Components/
|  |  |- Composables/
|  |  |- Layouts/
|  |  |- Pages/
|  |  |  |- Admin/
|  |  |  |- Bookings/
|  |  |  |- Clients/
|  |  |  |- System/
|  |  |  `- Users/
|  |  |- app.js
|  |  `- bootstrap.js
|  `- views/
|     |- app.blade.php
|     |- auth/
|     |- components/
|     |- pdf/invoice.blade.php
|     `- welcome.blade.php
|- routes/
|  |- auth.php
|  |- console.php
|  `- web.php
|- tests/
|  |- Feature/
|  |  |- Auth/
|  |  |- ProfileTest.php
|  |  `- SubdomainRouteTest.php
|  `- Unit/
|- compose.yaml
|- composer.json
|- composer.lock
|- package-lock.json
|- package.json
|- tailwind.config.js
|- vite.config.js
`- NEXUS_CONTEXT.txt
```
