<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends LegacyModel
{
    protected $table = 'OrderDetails';
    protected $primaryKey = 'OrderDetailID';

    protected function casts(): array
    {
        return [
            'Quantity' => 'integer',
            'Price' => 'decimal:2',
            'DiscountPercent' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'VariantID', 'VariantID');
    }
}
