<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Size extends LegacyModel
{
    protected $table = 'Sizes';
    protected $primaryKey = 'SizeID';

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'SizeID', 'SizeID');
    }
}
