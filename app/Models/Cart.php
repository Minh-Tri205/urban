<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends LegacyModel
{
    protected $table = 'Carts';
    protected $primaryKey = 'CartID';

    protected function casts(): array
    {
        return [
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerID', 'CustomerID');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'CartID', 'CartID');
    }
}
