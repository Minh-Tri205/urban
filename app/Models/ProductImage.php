<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends LegacyModel
{
    protected $table = 'ProductImages';
    protected $primaryKey = 'ImageID';

    protected function casts(): array
    {
        return [
            'IsPrimary' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function url(): string
    {
        return asset('Content/img/products/' . $this->ImageUrl);
    }
}
