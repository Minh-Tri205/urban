<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends LegacyModel
{
    protected $table = 'Categories';
    protected $primaryKey = 'CategoryID';

    public function categoryGroup(): BelongsTo
    {
        return $this->belongsTo(CategoryGroup::class, 'GroupID', 'GroupID');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'CategoryID', 'CategoryID');
    }
}
