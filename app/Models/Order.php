<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends LegacyModel
{
    protected $table = 'Orders';
    protected $primaryKey = 'OrderID';

    protected function casts(): array
    {
        return [
            'OrderDate' => 'datetime',
            'TotalAmount' => 'decimal:2',
            'ShippingFee' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerID', 'CustomerID');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'OrderID', 'OrderID');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'OrderID', 'OrderID');
    }

    // Tương đương các thuộc tính [NotMapped] trong Order.partial.cs
    public function statusEnum(): ?OrderStatus
    {
        return OrderStatus::parse($this->Status);
    }

    public function paymentMethodEnum(): ?PaymentMethod
    {
        return PaymentMethod::tryFrom((string) $this->PaymentMethod);
    }

    public function shippingMethodEnum(): ?ShippingMethod
    {
        return ShippingMethod::tryFrom((string) $this->ShippingMethod);
    }
}
