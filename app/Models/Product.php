<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends LegacyModel
{
    protected $table = 'Products';
    protected $primaryKey = 'ProductID';

    protected function casts(): array
    {
        return [
            'BasePrice' => 'decimal:2',
            'DiscountPercent' => 'decimal:2',
            'IsActive' => 'boolean',
            'CreatedAt' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'CategoryID', 'CategoryID');
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'ProductID', 'ProductID');
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'ProductID', 'ProductID');
    }

    /** Ảnh chính (IsPrimary = 1), nếu không có thì lấy ảnh đầu tiên. */
    public function primaryImage(): ?ProductImage
    {
        return $this->productImages->firstWhere('IsPrimary', true) ?? $this->productImages->first();
    }

    public function primaryImageUrl(string $fallback = 'default.jpg'): string
    {
        return asset('Content/img/products/' . ($this->primaryImage()?->ImageUrl ?? $fallback));
    }
}
