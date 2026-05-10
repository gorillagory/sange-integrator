# Seeding Strategy

## Goal

Create a deterministic bootstrap for a fresh control database:

1. Role catalog must exist first.
2. Genesis super admin must always be present with known credentials.
3. Core Bayam Group companies must always exist.
4. Module catalog and company-module assignments must be idempotent.
5. Optional seeders should not break fresh environments.

## Seeder Order

Defined in `DatabaseSeeder`:

1. `RbacSeeder`
2. `GenesisSeeder`
3. `ModuleSeeder`
4. `CompanyModuleSeeder`
5. `SchemaSeeder`
6. `TemplateSeeder`
7. `DocumentTemplateSeeder` (safe-skip if table missing)

## Canonical Genesis Identity

- Name: `Gorilla Gorriball`
- Email: `gori@bayam.com.my`
- Password: `passG03r1lenc3`
- Role: `super_admin` on global team (`company_id = 0`)

## Canonical Group and Companies

Main group:
- `Bayam Group`

Companies:
- `Bayamedic Services Sdn Bhd` (`subdomain=bner`, `db=sange_tenant_bner`, `industry=medical`)
- `Bayam Travel Sdn Bhd` (`subdomain=bt`, `db=sange_tenant_bt`, `industry=travel`)
- `Bayam Enterprise Sdn Bhd` (`subdomain=enterprise`, `db=sange_tenant_enterprise`, `industry=enterprise`)

## Runbook (Fresh Start with Sail)

```bash
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan tenant:migrate
```

Optional tenant migration for one tenant:

```bash
./vendor/bin/sail artisan tenant:migrate bt
```

## Notes

- `CompanyModuleSeeder` is idempotent via `updateOrCreate` + `syncWithoutDetaching`.
- `DocumentTemplateSeeder` now skips cleanly if the table is not present on active connection.
- Keep all seed commands inside Sail to avoid host PHP version mismatch.
