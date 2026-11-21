<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends LegacyModel
{
    protected $table = 'Colors';
    protected $primaryKey = 'ColorID';

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'ColorID', 'ColorID');
    }
}
