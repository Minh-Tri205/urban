<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends LegacyModel
{
    protected $table = 'CartItems';
    protected $primaryKey = 'CartItemID';

    protected function casts(): array
    {
        return [
            'Quantity' => 'integer',
            'AddedAt' => 'datetime',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'CartID', 'CartID');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'VariantID', 'VariantID');
    }
}
