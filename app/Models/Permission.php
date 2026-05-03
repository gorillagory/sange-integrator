<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    // 🔒 Lock Spatie Permissions to the landlord database
    protected $connection = 'control';
}
