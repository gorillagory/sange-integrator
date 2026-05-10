# Architecture

## Topology

Application style:
- Laravel 13 backend
- Inertia + Vue 3 frontend for operational pages
- Domain-segmented multi-tenant routing
- PostgreSQL control plane + tenant databases

Primary domains:
- `sys.bayam.test` -> control plane (system admin surface)
- `{subdomain}.bayam.test` -> tenant plane (company-specific operational surface)

## Request Flow

1. User authenticates.
2. System routes run on `sys.bayam.test` with `role:super_admin|system_admin` for control endpoints.
3. Tenant routes run on `{subdomain}.bayam.test` through `IdentifyTenant`.
4. `IdentifyTenant`:
   - resolves active company by subdomain
   - checks user membership or super-admin bypass
   - sets tenant DB name on `database.connections.tenant`
   - purges/reconnects `tenant` connection
   - sets default DB to `tenant`
   - sets Spatie team id
   - shares `currentCompany` to views/Inertia

5. Feature/middleware gates apply:
   - `company_module:*` for module availability
   - `super_admin_or_tenant_role:*` for role-based access

## Core Components

- Routing:
  - `routes/web.php` for system + tenant operational routes
  - `routes/auth.php` for auth endpoints
- Middleware:
  - `IdentifyTenant`
  - `EnsureCompanyModuleEnabled`
  - `AllowSuperAdminOrTenantRole`
  - `ResetPermissionTeam`
- Console:
  - `TenantMigrationManager` for cross-tenant migration orchestration
- Config:
  - `config/database.php` defines `control` and `tenant`
  - `config/permission.php` for Spatie RBAC tables and behavior
- Frontend:
  - Inertia + Vue pages under `resources/js/Pages`
  - Vite with Vue + Tailwind plugins

## Infra

- `compose.yaml` uses Laravel Sail service + Postgres 18.
- Tenant DB bootstrap SQL:
  - `sange_tenant_bner`
  - `sange_tenant_bt`
  - `sange_tenant_enterprise`
  - `sange_tenant_btech`

## Architectural Priorities

- Enforce branch protections around release branches.
- Standardize deployment/promote procedure.
- Document required local host mappings and environment setup in project README.
