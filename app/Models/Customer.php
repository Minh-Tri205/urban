<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends LegacyModel
{
    protected $table = 'Customers';
    protected $primaryKey = 'CustomerID';

    protected $hidden = ['PasswordHash'];

    protected function casts(): array
    {
        return [
            'IsActive' => 'boolean',
            'CreatedAt' => 'datetime',
            'RoleID' => 'integer',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'RoleID', 'RoleID');
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class, 'ProfileID', 'CustomerID');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'CustomerID', 'CustomerID');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'CustomerID', 'CustomerID');
    }

    /** RoleID 1 (Admin) hoặc 2 (Nhân viên) được vào trang quản lý. */
    public function isAdmin(): bool
    {
        return in_array($this->RoleID, [Role::ADMIN, Role::STAFF], true);
    }
}
