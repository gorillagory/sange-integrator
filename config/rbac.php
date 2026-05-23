<?php

$allTenantPermissions = [
    'tenant.dashboard.view',
    'service_records.view',
    'service_records.capture',
    'service_records.status.manage',
    'service_records.document.manage',
    'service_records.documents.generate',
    'clients.view',
    'clients.manage',
    'reports.view',
    'schemas.view',
    'schemas.manage',
    'documents.view',
    'documents.manage',
    'rbac.view',
    'rbac.manage',
];

$allGlobalPermissions = [
    'system.dashboard.view',
    'system.blueprints.view',
    'system.blueprints.manage',
    'system.users.view',
    'system.users.manage',
    'system.companies.view',
    'system.companies.manage',
    'system.audit_logs.view',
    'system.rbac.view',
    'system.rbac.manage',
];

return [
    'global_permissions' => $allGlobalPermissions,

    'global_role_permissions' => [
        'super_admin' => $allGlobalPermissions,
        'system_admin' => $allGlobalPermissions,
    ],

    'tenant_permissions' => $allTenantPermissions,

    'tenant_role_permissions' => [
        'agency_admin' => $allTenantPermissions,
        'booking_manager' => $allTenantPermissions,
        'travel_agent' => $allTenantPermissions,
        'document_manager' => $allTenantPermissions,
    ],
];
