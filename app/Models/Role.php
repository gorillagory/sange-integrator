<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // 🔒 Lock Spatie Roles to the landlord database
    protected $connection = 'control';
}
