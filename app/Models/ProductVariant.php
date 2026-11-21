<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends LegacyModel
{
    protected $table = 'ProductVariants';
    protected $primaryKey = 'VariantID';

    protected function casts(): array
    {
        return [
            'Stock' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'ColorID', 'ColorID');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'SizeID', 'SizeID');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'VariantID', 'VariantID');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'VariantID', 'VariantID');
    }
}
