<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends LegacyModel
{
    protected $table = 'OrderStatusHistory';
    protected $primaryKey = 'HistoryID';

    protected function casts(): array
    {
        return [
            'ChangedAt' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }
}
