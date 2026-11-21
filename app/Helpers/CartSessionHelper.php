<?php

namespace App\Helpers;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;

/**
 * Port từ Helpers/CartSessionHelper.cs.
 * Giỏ hàng của khách lưu trong session "Cart", mỗi dòng tương đương CartItemViewModel:
 * ['ProductID' => int, 'VariantID' => int, 'Quantity' => int]
 */
class CartSessionHelper
{
    public const SESSION_KEY = 'Cart';

    /** @return list<array{ProductID:int, VariantID:int, Quantity:int}> */
    public function cart(): array
    {
        return array_values(session(self::SESSION_KEY, []));
    }

    /** @param list<array{ProductID:int, VariantID:int, Quantity:int}> $items */
    public function save(array $items): void
    {
        session([self::SESSION_KEY => array_values($items)]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /** Số dòng trong giỏ (badge trên header). */
    public function count(): int
    {
        return count($this->cart());
    }

    /** @return Collection<int, ProductVariant> */
    public function productsInCart(): Collection
    {
        $ids = array_column($this->cart(), 'VariantID');

        return ProductVariant::whereIn('VariantID', $ids)->get();
    }
}
