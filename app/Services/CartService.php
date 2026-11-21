<?php

namespace App\Services;

use App\Helpers\CartSessionHelper;
use App\Helpers\CustomerSession;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Port các hàm GetOrCreateCart, UpdateCartSession (CartController.cs)
 * và MergeGuestCartToUserCart (AccountController.cs).
 * Giỏ hàng lưu trong DB; khách chưa đăng nhập được nhận diện bằng cookie CartToken.
 */
class CartService
{
    public const COOKIE = 'CartToken';
    private const COOKIE_MINUTES = 60 * 24 * 30;

    public function __construct(private CartSessionHelper $cartSession)
    {
    }

    public function getOrCreateCart(): Cart
    {
        if ($cart = $this->currentCart()) {
            return $cart;
        }

        $customerId = CustomerSession::id();

        if ($customerId !== null) {
            return Cart::firstOrCreate(
                ['CustomerID' => $customerId],
                ['CartToken' => (string) Str::uuid(), 'CreatedAt' => now(), 'UpdatedAt' => now()]
            );
        }

        $cart = Cart::create([
            'CustomerID' => null,
            'CartToken' => (string) Str::uuid(),
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
        ]);

        Cookie::queue(self::COOKIE, $cart->CartToken, self::COOKIE_MINUTES, httpOnly: true);

        return $cart;
    }

    /** Giỏ hiện tại nếu đã có (không tạo mới, tránh sinh giỏ rỗng khi chỉ xem trang). */
    public function currentCart(): ?Cart
    {
        $customerId = CustomerSession::id();

        return $customerId !== null
            ? Cart::where('CustomerID', $customerId)->first()
            : $this->findGuestCart();
    }

    public function findGuestCart(): ?Cart
    {
        $token = request()->cookie(self::COOKIE);

        if (! is_string($token) || $token === '') {
            return null;
        }

        return Cart::where('CartToken', $token)->whereNull('CustomerID')->first();
    }

    /** @return Collection<int, CartItem> */
    public function items(?Cart $cart): Collection
    {
        if ($cart === null) {
            return new Collection();
        }

        return $cart->cartItems()
            ->with(['productVariant.product.productImages', 'productVariant.color', 'productVariant.size'])
            ->orderBy('CartItemID')
            ->get();
    }

    /** Bản gốc giảm 30% vào ngày trùng tháng (1/1, 2/2, ..., 12/12). */
    public function discountPercentToday(): int
    {
        return now()->day === now()->month ? 30 : 0;
    }

    /** Gộp giỏ của khách vãng lai vào giỏ của tài khoản vừa đăng nhập, cộng dồn số lượng nếu trùng biến thể. */
    public function mergeGuestCartIntoCustomer(int $customerId): void
    {
        $guestCart = $this->findGuestCart();

        if ($guestCart !== null) {
            DB::transaction(function () use ($guestCart, $customerId) {
                $userCart = Cart::firstOrCreate(
                    ['CustomerID' => $customerId],
                    ['CartToken' => (string) Str::uuid(), 'CreatedAt' => now(), 'UpdatedAt' => now()]
                );

                foreach ($guestCart->cartItems as $guestItem) {
                    $existing = CartItem::where('CartID', $userCart->CartID)
                        ->where('VariantID', $guestItem->VariantID)
                        ->first();

                    if ($existing) {
                        $existing->increment('Quantity', $guestItem->Quantity);
                    } else {
                        CartItem::create([
                            'CartID' => $userCart->CartID,
                            'VariantID' => $guestItem->VariantID,
                            'Quantity' => $guestItem->Quantity,
                            'AddedAt' => now(),
                        ]);
                    }
                }

                $guestCart->cartItems()->delete();
                $guestCart->delete();
                $userCart->update(['UpdatedAt' => now()]);
            });

            Cookie::queue(Cookie::forget(self::COOKIE));
        }

        $this->refreshSession(Cart::where('CustomerID', $customerId)->first());
    }

    /** Đồng bộ session "Cart" (dùng cho badge trên header) theo giỏ trong DB. */
    public function refreshSession(?Cart $cart): void
    {
        if ($cart === null) {
            $this->cartSession->clear();

            return;
        }

        $items = $cart->cartItems()->with('productVariant:VariantID,ProductID')->get()
            ->map(fn (CartItem $item) => [
                'ProductID' => (int) $item->productVariant?->ProductID,
                'VariantID' => (int) $item->VariantID,
                'Quantity' => (int) $item->Quantity,
            ])
            ->all();

        $this->cartSession->save($items);
    }
}
