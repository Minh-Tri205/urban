<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends LegacyModel
{
    public const ADMIN = 1;
    public const STAFF = 2;
    public const CUSTOMER = 3;

    protected $table = 'Roles';
    protected $primaryKey = 'RoleID';

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'RoleID', 'RoleID');
    }
}
