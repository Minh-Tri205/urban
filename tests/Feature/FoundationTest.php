<?php

namespace Tests\Feature;

use App\Exceptions\InvalidOperationException;
use App\Helpers\CartSessionHelper;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Trang tạm để render layout khách hàng cùng thẻ sản phẩm
        Route::middleware('web')->get('/__layout-smoke', function () {
            return Blade::render(
                "@extends('layouts.app') @section('title', 'Smoke') @section('content')"
                . "@foreach (\$products as \$product) @include('partials.product-card') @endforeach @endsection",
                ['products' => Product::with('productImages')->take(2)->get()]
            );
        });
        Route::middleware(['web', 'admin'])->get('/__admin-smoke', fn () => 'admin-ok');
        app('router')->getRoutes()->refreshNameLookups();
    }

    public function test_model_relationships(): void
    {
        $product = Product::with(['category.categoryGroup', 'productImages', 'productVariants.color', 'productVariants.size'])->find(1);

        $this->assertSame('Áo Thun Nam Basic', $product->ProductName);
        $this->assertSame('Thời trang Nam', $product->category->categoryGroup->GroupName);
        $this->assertStringEndsWith('Content/img/products/product_001.jpg', $product->primaryImageUrl());
        $this->assertCount(25, $product->productVariants);
        $this->assertNotNull($product->productVariants->first()->color->ColorName);

        $customer = Customer::with(['customerProfile', 'role', 'orders'])->where('UserName', 'nguyenvana')->first();
        $this->assertSame('Nguyễn Văn A', $customer->customerProfile->FullName);
        $this->assertSame('Khách hàng', $customer->role->RoleName);
        $this->assertFalse($customer->isAdmin());
        $this->assertTrue(Customer::where('UserName', 'admin')->first()->isAdmin());

        $order = Order::with('orderDetails.productVariant.product')->find(1);
        $this->assertSame('Pending', $order->statusEnum()?->value);
        $this->assertNotEmpty($order->orderDetails->first()->productVariant->product->ProductName);
    }

    public function test_order_service_stock_changes(): void
    {
        $service = new OrderService();
        $before = ProductVariant::find(1)->Stock;

        $service->deductStock([['VariantID' => 1, 'Quantity' => 3]]);
        $this->assertSame($before - 3, ProductVariant::find(1)->Stock);

        $this->expectException(InvalidOperationException::class);
        $service->validateStockForOrder([['VariantID' => 1, 'Quantity' => 999999]]);
    }

    public function test_order_service_cancel_restores_stock_and_logs(): void
    {
        $service = new OrderService();
        $order = Order::with('orderDetails')->find(1);
        $variantId = $order->orderDetails->first()->VariantID;
        $before = ProductVariant::find($variantId)->Stock;

        $order->update(['Status' => 'Cancelled']);
        $service->onOrderCancelled($order);
        $service->logStatusChange($order->OrderID, 'Pending', 'Cancelled', 'admin', 'test');

        $this->assertGreaterThan($before, ProductVariant::find($variantId)->Stock);
        $this->assertDatabaseHas('OrderStatusHistory', ['OrderID' => 1, 'NewStatus' => 'Cancelled', 'ChangedBy' => 'admin']);
    }

    public function test_layout_renders_menu_cart_badge_and_product_cards(): void
    {
        $response = $this->withSession([CartSessionHelper::SESSION_KEY => [
            ['ProductID' => 1, 'VariantID' => 1, 'Quantity' => 2],
            ['ProductID' => 2, 'VariantID' => 26, 'Quantity' => 1],
        ]])->get('/__layout-smoke');

        $response->assertOk()
            ->assertSee('<meta name="csrf-token"', false)
            ->assertSee('Thời trang Nam')
            ->assertSee('Áo thun Nữ')
            ->assertSee('Content/img/products/product_001.jpg', false)
            ->assertSee('199.000đ')
            ->assertSee('rounded-pill bg-danger', false)
            ->assertSee(route('account.login'), false)
            ->assertDontSee('Đăng xuất');
    }

    public function test_layout_shows_account_menu_when_logged_in(): void
    {
        $admin = Customer::where('UserName', 'admin')->first();

        $this->withSession(['CustomerID' => $admin->CustomerID, 'RoleID' => 1])
            ->get('/__layout-smoke')
            ->assertOk()
            ->assertSee('Đăng xuất')
            ->assertSee('Trang quản lý');
    }

    public function test_toast_shows_flash_message(): void
    {
        $this->withSession(['Success' => 'Đặt hàng thành công!'])
            ->get('/__layout-smoke')
            ->assertSee('urban-toast-success', false)
            ->assertSee('Đặt hàng thành công!');
    }

    public function test_admin_middleware(): void
    {
        $this->get('/__admin-smoke')->assertRedirect(route('account.login'));

        $customer = Customer::where('UserName', 'nguyenvana')->first();
        $this->withSession(['CustomerID' => $customer->CustomerID])
            ->get('/__admin-smoke')
            ->assertRedirect(route('home.index'))
            ->assertSessionHas('Error');

        $admin = Customer::where('UserName', 'admin')->first();
        $this->withSession(['CustomerID' => $admin->CustomerID])
            ->get('/__admin-smoke')
            ->assertOk()
            ->assertSee('admin-ok');
    }

    public function test_inactive_customer_is_treated_as_logged_out(): void
    {
        $customer = Customer::where('UserName', 'nguyenvana')->first();
        $customer->update(['IsActive' => false]);

        $this->withSession(['CustomerID' => $customer->CustomerID])
            ->get('/__layout-smoke')
            ->assertDontSee('Đăng xuất');
    }

    public function test_case_insensitive_urls_redirect_to_canonical_route(): void
    {
        $this->get('/product/details/5?x=1')->assertRedirect(url('/Product/Details/5') . '?x=1')->assertStatus(301);
        $this->get('/Product')->assertRedirect(url('/Product/Index'));
        $this->get('/admin')->assertRedirect(url('/Admin/Dashboard/Index'));
        $this->get('/account/login')->assertRedirect(url('/Account/Login'));
    }

    public function test_unknown_url_shows_custom_404_page(): void
    {
        $this->get('/khong-ton-tai/abc')
            ->assertNotFound()
            ->assertSee('Error 404')
            ->assertSee('Rất tiếc, trang bạn đang tìm kiếm không tồn tại.');
    }
}
