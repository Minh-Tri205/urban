<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfile extends LegacyModel
{
    protected $table = 'CustomerProfile';
    protected $primaryKey = 'ProfileID';
    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'DateOfBirth' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'ProfileID', 'CustomerID');
    }
}
