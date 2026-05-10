# Routes And User Flows

## Route Map

From `routes/web.php` and `routes/auth.php`.

System domain: `sys.bayam.test`

- Middleware: `web`, `auth`, `ResetPermissionTeam`, `role:super_admin|system_admin`
- `GET /lobby` -> `Dashboard` (Inertia page)
- `GET /dashboard` -> `System/Dashboard` (Inertia page)
- `GET/POST /companies` -> company management
- `GET/POST/PUT /blueprints...` -> blueprint CRUD
- `GET/POST/PUT /users...` -> system user management

Tenant domain: `{subdomain}.bayam.test`

- Base middleware: `web`, `auth`, `IdentifyTenant`
- `GET /dashboard` -> `TenantDashboard` (Inertia page with `currentCompany`)

Tenant module group: `company_module:travel.booking`
- `GET/POST /bookings...`, invoice update/download
- `GET/POST /clients...`
- `POST/PUT /contracts...`
- `GET /reports`

Tenant admin schemas group:
- Middleware: `company_module:travel.schemas`, `super_admin_or_tenant_role:agency_admin`
- `GET/POST/PUT/DELETE /admin/schemas...`

Tenant admin documents group:
- Middleware: `company_module:travel.documents`, `super_admin_or_tenant_role:agency_admin,document_manager`
- `GET/POST/PUT/DELETE /admin/documents...`
- `GET /admin/documents/{subdomain}/{id}/preview`

Global auth routes:
- From `routes/auth.php` (`/login`, `/register`, `/logout`, password reset, email verify)
- Profile routes under `auth` middleware (`/profile`)

## User Journey (Current)

1. User signs in.
2. User accesses control domain for governance/configuration tasks.
3. User enters a tenant domain.
4. Tenant middleware resolves and validates tenant access.
5. User sees only modules enabled for that company.
6. Role-gated routes unlock schema/document admin capabilities.

## Release Flow (Git)

1. Feature branches merge into `pr`.
2. `pr` promotes to `staging`.
3. `staging` promotes to `production`.
4. `production` promotes to `main`.
