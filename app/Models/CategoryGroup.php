<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryGroup extends LegacyModel
{
    protected $table = 'CategoryGroups';
    protected $primaryKey = 'GroupID';

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'GroupID', 'GroupID');
    }
}
