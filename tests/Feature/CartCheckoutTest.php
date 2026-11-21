<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Ngày 13/9: không trùng ngày-tháng nên không có giảm giá 30%
        $this->travelTo(now()->setDate(2026, 9, 13)->setTime(10, 0));

        $this->customer = Customer::with('customerProfile')->where('UserName', 'nguyenvana')->first();
        Cart::where('CustomerID', $this->customer->CustomerID)->delete();
    }

    private function asCustomer(): static
    {
        return $this->withSession(['CustomerID' => $this->customer->CustomerID]);
    }

    private function variantWithStock(int $min = 5, ?int $productId = null): ProductVariant
    {
        return ProductVariant::with('product')
            ->where('Stock', '>=', $min)
            ->when($productId, fn ($q) => $q->where('ProductID', $productId))
            ->whereHas('product', fn ($q) => $q->where('IsActive', true))
            ->orderBy('VariantID')
            ->firstOrFail();
    }

    private function addToCart(ProductVariant $variant, int $quantity = 1)
    {
        return $this->post('/Cart/AddToCart', [
            'ProductID' => $variant->ProductID,
            'ColorId' => $variant->ColorID,
            'SizeId' => $variant->SizeID,
            'Quantity' => $quantity,
        ]);
    }

    private function customerCart(): Cart
    {
        return Cart::where('CustomerID', $this->customer->CustomerID)->firstOrFail();
    }

    private function checkoutPayload(array $profile = []): array
    {
        return [
            'CustomerProfile' => $profile + [
                'FullName' => 'Nguyễn Văn A',
                'PhoneNumber' => '0901234567',
                'City' => 'Thành phố Hồ Chí Minh',
                'District' => 'Quận Tân Phú',
                'Address' => '140 Lê Trọng Tấn',
            ],
            'ShippingMethod' => 'Standard',
            'PaymentMethod' => 'COD',
        ];
    }

    // ===== Giỏ hàng =====

    public function test_empty_cart_page_does_not_create_cart(): void
    {
        $this->get('/Cart/Index')->assertOk()->assertSee('Giỏ hàng trống');
        $this->assertSame(0, Cart::whereNull('CustomerID')->where('CreatedAt', '>=', now()->subMinute())->count());
    }

    public function test_guest_add_to_cart_uses_cookie_token(): void
    {
        $variant = $this->variantWithStock();

        $response = $this->addToCart($variant, 2)
            ->assertRedirect(route('product.index'))
            ->assertSessionHas('Success', 'Đã thêm sản phẩm vào giỏ hàng.');

        $cookie = collect($response->headers->getCookies())->firstWhere(fn ($c) => $c->getName() === CartService::COOKIE);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());

        $cart = Cart::whereNull('CustomerID')->latest('CartID')->first();
        $this->assertSame(2, CartItem::where('CartID', $cart->CartID)->where('VariantID', $variant->VariantID)->value('Quantity'));
        $this->assertSame([['ProductID' => $variant->ProductID, 'VariantID' => $variant->VariantID, 'Quantity' => 2]], session('Cart'));

        // Lần sau cùng cookie: cộng dồn vào đúng giỏ
        $this->withCookie(CartService::COOKIE, $cart->CartToken);
        $this->addToCart($variant, 1);
        $this->assertSame(3, CartItem::where('CartID', $cart->CartID)->where('VariantID', $variant->VariantID)->value('Quantity'));

        $this->get('/Cart/Index')->assertOk()
            ->assertSee($variant->product->ProductName)
            ->assertSee(vnd($variant->product->BasePrice * 3));
    }

    public function test_add_to_cart_validation(): void
    {
        $variant = $this->variantWithStock();

        $this->post('/Cart/AddToCart', ['ProductID' => $variant->ProductID, 'Quantity' => 1])
            ->assertRedirect(route('product.details', $variant->ProductID))
            ->assertSessionHas('Error', 'Dữ liệu giỏ hàng không hợp lệ.');

        $this->post('/Cart/AddToCart', ['ProductID' => $variant->ProductID, 'ColorId' => 999, 'SizeId' => 999, 'Quantity' => 1])
            ->assertSessionHas('Error', 'Biến thể sản phẩm không tồn tại.');

        $this->addToCart($variant, $variant->Stock + 1)
            ->assertSessionHas('Error', "Chỉ còn {$variant->Stock} sản phẩm trong kho.");

        $this->asCustomer()->addToCart($variant, $variant->Stock);
        $this->asCustomer()->addToCart($variant, 1)
            ->assertSessionHas('Error', "Bạn chỉ có thể mua tối đa {$variant->Stock} sản phẩm này.");

        Product::whereKey($variant->ProductID)->update(['IsActive' => false]);
        $this->addToCart($variant, 1)->assertNotFound();
    }

    public function test_update_quantity_remove_and_clear(): void
    {
        $v1 = $this->variantWithStock(5);
        $v2 = $this->variantWithStock(5, Product::where('ProductID', '!=', $v1->ProductID)->where('IsActive', true)->value('ProductID'));

        $this->asCustomer()->addToCart($v1, 1);
        $this->asCustomer()->addToCart($v2, 1);
        $cart = $this->customerCart();

        $this->asCustomer()->postJson('/Cart/UpdateQuantity', ['variantId' => $v1->VariantID, 'quantity' => 3])
            ->assertJson([
                'success' => true,
                'itemTotal' => n0($v1->product->BasePrice * 3),
                'grandTotal' => n0($v1->product->BasePrice * 3 + $v2->product->BasePrice),
            ]);

        $this->asCustomer()->postJson('/Cart/UpdateQuantity', ['variantId' => $v1->VariantID, 'quantity' => 0])
            ->assertJson(['success' => false, 'message' => 'Số lượng phải lớn hơn 0.']);
        $this->asCustomer()->postJson('/Cart/UpdateQuantity', ['variantId' => $v1->VariantID, 'quantity' => $v1->Stock + 1])
            ->assertJson(['success' => false, 'message' => "Chỉ còn {$v1->Stock} sản phẩm trong kho."]);
        $this->asCustomer()->postJson('/Cart/UpdateQuantity', ['variantId' => 999999, 'quantity' => 1])
            ->assertJson(['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng.']);

        $this->asCustomer()->post("/Cart/Remove/{$v1->VariantID}")
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('Success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
        $this->assertSame(1, CartItem::where('CartID', $cart->CartID)->count());

        $this->asCustomer()->get("/Cart/Remove/{$v2->VariantID}")->assertNotFound();

        $this->asCustomer()->post('/Cart/Clear')->assertRedirect(route('cart.index'));
        $this->assertSame(0, CartItem::where('CartID', $cart->CartID)->count());
        $this->assertSame([], session('Cart'));
    }

    // ===== Checkout =====

    public function test_checkout_requires_login_and_items(): void
    {
        $this->get('/Cart/Checkout')->assertRedirect(route('account.login'));
        $this->post('/Cart/Checkout', $this->checkoutPayload())->assertRedirect(route('account.login'));

        $this->asCustomer()->get('/Cart/Checkout')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('Error', 'Bạn chưa có sản phẩm nào trong giỏ hàng');
    }

    public function test_checkout_page_shows_summary(): void
    {
        $variant = $this->variantWithStock();
        $this->asCustomer()->addToCart($variant, 2);

        $this->customer->customerProfile->update(['City' => 'Hồ Chí Minh']);
        $expected = $variant->product->BasePrice * 2;

        $this->asCustomer()->get('/Cart/Checkout')->assertOk()
            ->assertSee('Xác nhận thông tin đặt hàng')
            ->assertSee($this->customer->customerProfile->FullName)
            ->assertSee(vnd($expected))
            ->assertSee(vnd(20000))
            ->assertSee(vnd($expected + 20000))
            ->assertDontSee('Giảm giá');
    }

    public function test_successful_checkout_creates_order_deducts_stock_and_empties_cart(): void
    {
        $v1 = $this->variantWithStock(5);
        $v2 = $this->variantWithStock(5, Product::where('ProductID', '!=', $v1->ProductID)->where('IsActive', true)->value('ProductID'));
        $this->asCustomer()->addToCart($v1, 2);
        $this->asCustomer()->addToCart($v2, 1);

        $response = $this->asCustomer()->post('/Cart/Checkout', $this->checkoutPayload(['PhoneNumber' => '090 123 4567']));

        $order = Order::with('orderDetails')->where('CustomerID', $this->customer->CustomerID)->latest('OrderID')->first();
        $response->assertRedirect(route('order.index'))->assertSessionHas('Success', "Đặt hàng thành công. Mã đơn hàng #{$order->OrderID}.");

        $productTotal = $v1->product->BasePrice * 2 + $v2->product->BasePrice;
        $this->assertSame('Pending', $order->Status);
        $this->assertSame('COD', $order->PaymentMethod);
        $this->assertSame('Standard', $order->ShippingMethod);
        $this->assertSame('0901234567', $order->PhoneNumber);
        $this->assertSame('140 Lê Trọng Tấn, Quận Tân Phú, Thành phố Hồ Chí Minh', $order->ShippingAddress);
        $this->assertEquals(20000, $order->ShippingFee);
        $this->assertEquals($productTotal + 20000, $order->TotalAmount);
        $this->assertCount(2, $order->orderDetails);
        $this->assertEquals(0, $order->orderDetails->first()->DiscountPercent);

        $this->assertSame($v1->Stock - 2, $v1->fresh()->Stock);
        $this->assertSame($v2->Stock - 1, $v2->fresh()->Stock);
        $this->assertSame(0, CartItem::where('CartID', $this->customerCart()->CartID)->count());
        $this->assertSame([], session('Cart'));
        $this->assertTrue(OrderStatusHistory::where('OrderID', $order->OrderID)->where('NewStatus', 'Pending')->whereNull('OldStatus')->exists());

        $this->asCustomer()->get('/Order/Index')->assertOk()
            ->assertSee("Đơn hàng #{$order->OrderID}")
            ->assertSee($v1->product->ProductName)
            ->assertSee(vnd($productTotal + 20000));
    }

    public function test_checkout_applies_day_equals_month_discount_and_intercity_fee(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 9));
        $variant = $this->variantWithStock();
        $this->asCustomer()->addToCart($variant, 1);

        $this->asCustomer()->get('/Cart/Checkout')->assertSee('Giảm giá (30%)');

        $this->asCustomer()->post('/Cart/Checkout', $this->checkoutPayload(['City' => 'Thành phố Hà Nội', 'District' => 'Quận Ba Đình']))
            ->assertRedirect(route('order.index'));

        $order = Order::with('orderDetails')->latest('OrderID')->first();
        $price = (float) $variant->product->BasePrice;
        $this->assertEquals(30000, $order->ShippingFee);
        $this->assertEquals(round($price * 0.7, 2) + 30000, (float) $order->TotalAmount);
        $this->assertEquals(30, $order->orderDetails->first()->DiscountPercent);
    }

    public function test_checkout_validation_errors_keep_input_and_do_not_create_order(): void
    {
        $variant = $this->variantWithStock();
        $this->asCustomer()->addToCart($variant, 1);
        $ordersBefore = Order::count();

        $this->asCustomer()->from('/Cart/Checkout')
            ->post('/Cart/Checkout', array_replace($this->checkoutPayload(['FullName' => 'A1', 'PhoneNumber' => '123']), ['PaymentMethod' => 'EWallet']))
            ->assertRedirect(route('cart.checkout'))
            ->assertSessionHasErrors()
            ->assertSessionHasInput('CustomerProfile.FullName', 'A1');

        $errors = session('errors')->all();
        $this->assertContains('Số điện thoại không hợp lệ.', $errors);
        $this->assertContains('Phương thức thanh toán không hợp lệ.', $errors);
        $this->assertSame($ordersBefore, Order::count());
        $this->assertSame($variant->Stock, $variant->fresh()->Stock);
    }

    public function test_checkout_rolls_back_when_stock_is_insufficient(): void
    {
        $variant = $this->variantWithStock(3);
        $this->asCustomer()->addToCart($variant, 3);
        $variant->update(['Stock' => 1]);
        $ordersBefore = Order::count();

        $this->asCustomer()->post('/Cart/Checkout', $this->checkoutPayload())
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('Error', "Sản phẩm {$variant->product->ProductName} không đủ hàng. Số lượng đặt: 3, còn lại: 1.");

        $this->assertSame($ordersBefore, Order::count());
        $this->assertSame(1, $variant->fresh()->Stock);
        $this->assertSame(1, CartItem::where('CartID', $this->customerCart()->CartID)->count());

        $this->asCustomer()->get('/Cart/Index')->assertSee('Chỉ còn 1 sản phẩm trong kho');
    }

    public function test_checkout_rejects_inactive_products(): void
    {
        $variant = $this->variantWithStock();
        $this->asCustomer()->addToCart($variant, 1);
        Product::whereKey($variant->ProductID)->update(['IsActive' => false]);

        $this->asCustomer()->post('/Cart/Checkout', $this->checkoutPayload())
            ->assertRedirect(route('cart.checkout'))
            ->assertSessionHasErrors();

        $this->assertStringContainsString('đã ngừng kinh doanh', implode(' ', session('errors')->all()));
    }

    // ===== Đơn hàng =====

    private function createOrderFor(Customer $customer, string $status, int $quantity = 2): Order
    {
        $variant = $this->variantWithStock();
        $order = Order::create([
            'CustomerID' => $customer->CustomerID,
            'OrderDate' => now(),
            'Status' => $status,
            'FullName' => 'Test',
            'PhoneNumber' => '0901234567',
            'ShippingAddress' => 'HCM',
            'TotalAmount' => 100000,
            'PaymentMethod' => 'COD',
            'ShippingMethod' => 'Standard',
            'ShippingFee' => 20000,
        ]);
        $order->orderDetails()->create(['VariantID' => $variant->VariantID, 'Quantity' => $quantity, 'Price' => 40000, 'DiscountPercent' => 0]);

        return $order;
    }

    public function test_order_index_filters_by_status(): void
    {
        $pending = $this->createOrderFor($this->customer, 'Pending');
        $completed = $this->createOrderFor($this->customer, 'Completed');

        $response = $this->asCustomer()->get('/Order/Index?status=Completed')->assertOk();
        $ids = $response->viewData('orders')->pluck('OrderID')->all();
        $this->assertContains($completed->OrderID, $ids);
        $this->assertNotContains($pending->OrderID, $ids);
        $this->assertSame(['Completed'], $response->viewData('orders')->pluck('Status')->unique()->values()->all());

        $other = Customer::where('RoleID', 3)->where('CustomerID', '!=', $this->customer->CustomerID)->first();
        $otherOrder = $this->createOrderFor($other, 'Pending');
        $this->assertNotContains($otherOrder->OrderID, $this->asCustomer()->get('/Order/Index')->viewData('orders')->pluck('OrderID')->all());

        $this->flushSession();
        $this->get('/Order/Index')->assertRedirect(route('account.login'));
    }

    public function test_customer_can_cancel_pending_order_and_stock_is_restored(): void
    {
        $order = $this->createOrderFor($this->customer, 'Processing', 2);
        $variant = ProductVariant::find($order->orderDetails()->value('VariantID'));

        $this->asCustomer()->post("/Order/Cancel/{$order->OrderID}")
            ->assertRedirect(route('order.index'))
            ->assertSessionHas('Success', 'Hủy đặt hàng thành công, cảm ơn quý khách.');

        $this->assertSame('Cancelled', $order->fresh()->Status);
        $this->assertSame($variant->Stock + 2, $variant->fresh()->Stock);
        $this->assertDatabaseHas('OrderStatusHistory', [
            'OrderID' => $order->OrderID, 'OldStatus' => 'Processing', 'NewStatus' => 'Cancelled', 'ChangedBy' => 'nguyenvana',
        ]);

        // Hủy lần 2: không hoàn kho thêm
        $this->asCustomer()->post("/Order/Cancel/{$order->OrderID}")->assertSessionHas('Error');
        $this->assertSame($variant->Stock + 2, $variant->fresh()->Stock);
    }

    public function test_cancel_rules(): void
    {
        $shipped = $this->createOrderFor($this->customer, 'Shipped');
        $this->asCustomer()->post("/Order/Cancel/{$shipped->OrderID}")
            ->assertSessionHas('Error', "Chỉ có thể hủy đơn hàng khi đơn hàng đang ở trạng thái 'Chờ xác nhận' hoặc 'Đang xử lý'. Đơn hàng đã được giao không thể hủy.");
        $this->assertSame('Shipped', $shipped->fresh()->Status);

        $other = Customer::where('RoleID', 3)->where('CustomerID', '!=', $this->customer->CustomerID)->first();
        $othersOrder = $this->createOrderFor($other, 'Pending');
        $this->asCustomer()->post("/Order/Cancel/{$othersOrder->OrderID}")
            ->assertSessionHas('Error', 'Bạn không có quyền hủy đơn hàng này.');
        $this->assertSame('Pending', $othersOrder->fresh()->Status);

        $this->asCustomer()->post('/Order/Cancel/999999')->assertSessionHas('Error', 'Đơn hàng không tồn tại.');
    }

    // ===== Thống kê =====

    public function test_thongke_index_and_details(): void
    {
        Order::where('CustomerID', $this->customer->CustomerID)->each(fn ($o) => $o->delete());
        $completed = $this->createOrderFor($this->customer, 'Completed');
        $this->createOrderFor($this->customer, 'Completed');
        $this->createOrderFor($this->customer, 'Cancelled');
        $pending = $this->createOrderFor($this->customer, 'Pending');

        $response = $this->asCustomer()->get('/ThongKe/Index')->assertOk()->assertSee('BÁO CÁO MUA HÀNG');
        $this->assertSame(4, $response->viewData('totalOrders'));
        $this->assertEquals(200000, $response->viewData('totalSpent'));
        $this->assertSame(2, $response->viewData('completed'));
        $this->assertSame(1, $response->viewData('shipping'));

        $this->asCustomer()->get("/ThongKe/Details/{$completed->OrderID}")->assertOk()
            ->assertSee("ĐƠN HÀNG #{$completed->OrderID}")
            ->assertSee('Hoàn thành')
            ->assertDontSee('Hủy đơn hàng');

        $this->asCustomer()->get("/ThongKe/Details/{$pending->OrderID}")->assertOk()->assertSee('Hủy đơn hàng');

        $this->asCustomer()->from("/ThongKe/Details/{$pending->OrderID}")
            ->post("/Order/Cancel/{$pending->OrderID}")
            ->assertRedirect(url("/ThongKe/Details/{$pending->OrderID}"));

        $other = Customer::where('RoleID', 3)->where('CustomerID', '!=', $this->customer->CustomerID)->first();
        $othersOrder = $this->createOrderFor($other, 'Pending');
        $this->asCustomer()->get("/ThongKe/Details/{$othersOrder->OrderID}")->assertNotFound();

        $this->flushSession();
        $this->get('/ThongKe/Index')->assertRedirect(route('account.login'));
    }

    public function test_guest_cart_merges_on_login_then_checkout(): void
    {
        $variant = $this->variantWithStock();
        $this->addToCart($variant, 2);
        $guestCart = Cart::whereNull('CustomerID')->latest('CartID')->first();

        $this->withCookie(CartService::COOKIE, $guestCart->CartToken)
            ->post('/Account/Login', ['UserName' => 'nguyenvana', 'Password' => '123456']);

        $this->assertSame(2, CartItem::where('CartID', $this->customerCart()->CartID)->value('Quantity'));

        $this->asCustomer()->post('/Cart/Checkout', $this->checkoutPayload())->assertRedirect(route('order.index'));
        $this->assertSame($variant->Stock - 2, $variant->fresh()->Stock);
    }

    public function test_cart_token_is_random_uuid(): void
    {
        $this->addToCart($this->variantWithStock(), 1);

        $this->assertTrue(Str::isUuid(Cart::whereNull('CustomerID')->latest('CartID')->value('CartToken')));
    }
}
