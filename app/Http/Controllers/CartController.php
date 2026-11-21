<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Exceptions\InvalidOperationException;
use App\Helpers\CustomerSession;
use App\Models\CartItem;
use App\Models\CustomerProfile;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Rules\VietnamesePhone;
use App\Services\CartService;
use App\Services\CheckoutValidationService;
use App\Services\OrderService;
use App\Services\ShippingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private ShippingService $shippingService,
    ) {
    }

    public function index()
    {
        $cart = $this->cartService->currentCart();
        $items = $this->cartService->items($cart);

        $this->cartService->refreshSession($cart);

        return view('cart.index', [
            'items' => $items,
            'totalAmount' => $items->sum(fn (CartItem $item) => $this->unitPrice($item) * $item->Quantity),
        ]);
    }

    public function addToCart(Request $request)
    {
        $productId = (int) $request->input('ProductID');
        $quantity = (int) $request->input('Quantity');
        $colorId = (int) $request->input('ColorId');
        $sizeId = (int) $request->input('SizeId');

        if ($productId <= 0 || $quantity <= 0 || $colorId <= 0 || $sizeId <= 0) {
            return $this->backToProduct($productId, 'Dữ liệu giỏ hàng không hợp lệ.');
        }

        $product = Product::where('IsActive', true)->find($productId);
        abort_if($product === null, 404);

        $variant = ProductVariant::where('ProductID', $productId)
            ->where('ColorID', $colorId)
            ->where('SizeID', $sizeId)
            ->first();

        if ($variant === null) {
            return $this->backToProduct($productId, 'Biến thể sản phẩm không tồn tại.');
        }

        if ($variant->Stock < $quantity) {
            return $this->backToProduct($productId, "Chỉ còn {$variant->Stock} sản phẩm trong kho.");
        }

        $cart = $this->cartService->getOrCreateCart();
        $existingItem = CartItem::where('CartID', $cart->CartID)->where('VariantID', $variant->VariantID)->first();

        if ($existingItem !== null) {
            $newQuantity = $existingItem->Quantity + $quantity;

            if ($newQuantity > $variant->Stock) {
                return $this->backToProduct($productId, "Bạn chỉ có thể mua tối đa {$variant->Stock} sản phẩm này.");
            }

            $existingItem->update(['Quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'CartID' => $cart->CartID,
                'VariantID' => $variant->VariantID,
                'Quantity' => $quantity,
                'AddedAt' => now(),
            ]);
        }

        $cart->update(['UpdatedAt' => now()]);
        $this->cartService->refreshSession($cart);

        return redirect()->route('product.index')->with('Success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function remove(?string $id = null)
    {
        $cart = $this->cartService->currentCart();

        if ($cart !== null && ctype_digit((string) $id)) {
            $deleted = CartItem::where('CartID', $cart->CartID)->where('VariantID', (int) $id)->delete();

            if ($deleted > 0) {
                $cart->update(['UpdatedAt' => now()]);
                $this->cartService->refreshSession($cart);

                return redirect()->route('cart.index')->with('Success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
            }
        }

        return redirect()->route('cart.index');
    }

    public function updateQuantity(Request $request)
    {
        try {
            $variantId = (int) $request->input('variantId');
            $quantity = (int) $request->input('quantity');

            if ($quantity <= 0) {
                return response()->json(['success' => false, 'message' => 'Số lượng phải lớn hơn 0.']);
            }

            $cart = $this->cartService->currentCart();
            $cartItem = $cart ? CartItem::where('CartID', $cart->CartID)->where('VariantID', $variantId)->first() : null;

            if ($cartItem === null) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng.']);
            }

            $variant = ProductVariant::with('product')->find($variantId);

            if ($variant === null || $variant->product === null) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
            }

            if ($quantity > $variant->Stock) {
                return response()->json(['success' => false, 'message' => "Chỉ còn {$variant->Stock} sản phẩm trong kho."]);
            }

            $cartItem->update(['Quantity' => $quantity]);
            $cart->update(['UpdatedAt' => now()]);

            $grandTotal = $this->cartService->items($cart)
                ->sum(fn (CartItem $item) => $this->unitPrice($item) * $item->Quantity);

            $this->cartService->refreshSession($cart);

            return response()->json([
                'success' => true,
                'itemTotal' => n0((float) $variant->product->BasePrice * $quantity),
                'grandTotal' => n0($grandTotal),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
        }
    }

    public function checkout()
    {
        $items = $this->cartService->items($this->cartService->currentCart());

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('Error', 'Bạn chưa có sản phẩm nào trong giỏ hàng');
        }

        $customer = CustomerSession::user();
        $profile = $customer->customerProfile ?? new CustomerProfile(['ProfileID' => $customer->CustomerID]);

        return view('cart.checkout', [
            'profile' => $profile,
            'summary' => $this->summarize($items, $profile->City),
            'items' => $items,
        ]);
    }

    public function checkoutPost(Request $request, CheckoutValidationService $validationService, OrderService $orderService)
    {
        $cart = $this->cartService->currentCart();
        $items = $this->cartService->items($cart);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('Error', 'Bạn chưa có sản phẩm nào trong giỏ hàng');
        }

        $profileInput = $request->input('CustomerProfile');
        $profile = collect(['FullName', 'PhoneNumber', 'Address', 'District', 'City'])
            ->mapWithKeys(fn ($field) => [$field => is_array($profileInput) ? trim((string) ($profileInput[$field] ?? '')) : ''])
            ->all();

        // Tổng tiền luôn tính lại ở server theo giỏ hàng và tỉnh/thành người nhận
        $summary = $this->summarize($items, $profile['City']);
        $checkout = [
            'CustomerProfile' => $profile,
            'PaymentMethod' => (string) $request->input('PaymentMethod', PaymentMethod::COD->value),
            'ShippingMethod' => (string) $request->input('ShippingMethod', ShippingMethod::Standard->value),
        ] + $summary;

        $result = $validationService->validateCheckout($checkout, $items);

        foreach (['Address' => 255, 'District' => 100, 'City' => 100] as $field => $max) {
            if (mb_strlen($profile[$field]) > $max) {
                $result->addError("Thông tin địa chỉ quá dài (tối đa {$max} ký tự).");
            }
        }

        foreach ($items as $item) {
            if (! $item->productVariant?->product?->IsActive) {
                $name = $item->productVariant?->product?->ProductName ?? "mã {$item->VariantID}";
                $result->addError("Sản phẩm {$name} đã ngừng kinh doanh. Vui lòng xóa khỏi giỏ hàng.");
            }
        }

        if (! $result->isValid) {
            return redirect()->route('cart.checkout')->withErrors($result->errors)->withInput();
        }

        $customer = CustomerSession::user();

        try {
            $order = DB::transaction(function () use ($items, $profile, $summary, $customer, $cart, $orderService) {
                // Khóa và trừ tồn kho trước; thiếu hàng sẽ ném InvalidOperationException và rollback
                $orderService->deductStock($items);

                $order = Order::create([
                    'CustomerID' => $customer->CustomerID,
                    'OrderDate' => now(),
                    'Status' => OrderStatus::Pending->value,
                    'FullName' => $profile['FullName'],
                    'PhoneNumber' => VietnamesePhone::normalize($profile['PhoneNumber']),
                    'ShippingAddress' => mb_substr(implode(', ', array_filter(
                        [$profile['Address'], $profile['District'], $profile['City']],
                        fn ($part) => $part !== ''
                    )), 0, 255),
                    'TotalAmount' => $summary['GrandTotal'],
                    'PaymentMethod' => PaymentMethod::COD->value,
                    'ShippingMethod' => ShippingMethod::Standard->value,
                    'ShippingFee' => $summary['ShippingFee'],
                ]);

                foreach ($items as $item) {
                    OrderDetail::create([
                        'OrderID' => $order->OrderID,
                        'VariantID' => $item->VariantID,
                        'Quantity' => $item->Quantity,
                        'Price' => $item->productVariant->product->BasePrice,
                        'DiscountPercent' => $summary['DiscountPercent'],
                    ]);
                }

                CartItem::where('CartID', $cart->CartID)->delete();
                $cart->update(['UpdatedAt' => now()]);

                $orderService->logStatusChange(
                    $order->OrderID,
                    null,
                    OrderStatus::Pending->value,
                    $customer->UserName ?? 'Customer',
                    'Order created'
                );

                return $order;
            });
        } catch (InvalidOperationException $e) {
            return redirect()->route('cart.index')->with('Error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('cart.index')->with('Error', 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.');
        }

        $this->cartService->refreshSession($cart);

        return redirect()->route('order.index')->with('Success', "Đặt hàng thành công. Mã đơn hàng #{$order->OrderID}.");
    }

    public function clear()
    {
        $cart = $this->cartService->currentCart();

        if ($cart !== null) {
            CartItem::where('CartID', $cart->CartID)->delete();
        }

        $this->cartService->refreshSession($cart);

        return redirect()->route('cart.index');
    }

    private function unitPrice(CartItem $item): float
    {
        return (float) ($item->productVariant?->product?->BasePrice ?? 0);
    }

    /** @return array{SubTotal: float, TotalDiscount: float, ShippingFee: float, GrandTotal: float, DiscountPercent: int} */
    private function summarize(Collection $items, ?string $city): array
    {
        $discountPercent = $this->cartService->discountPercentToday();
        $subTotal = round($items->sum(fn (CartItem $item) => $this->unitPrice($item) * $item->Quantity), 2);
        $totalDiscount = round($subTotal * $discountPercent / 100, 2);
        $shippingFee = $this->shippingService->calculateShippingFee($city);

        return [
            'SubTotal' => $subTotal,
            'TotalDiscount' => $totalDiscount,
            'ShippingFee' => $shippingFee,
            'GrandTotal' => round($subTotal - $totalDiscount + $shippingFee, 2),
            'DiscountPercent' => $discountPercent,
        ];
    }

    private function backToProduct(int $productId, string $message)
    {
        return $productId > 0
            ? redirect()->route('product.details', $productId)->with('Error', $message)
            : redirect()->route('product.index')->with('Error', $message);
    }
}
