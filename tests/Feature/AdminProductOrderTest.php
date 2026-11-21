<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminProductOrderTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private Customer $admin;
    private string $imageDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Customer::where('UserName', 'admin')->first();
        $this->imageDir = storage_path('framework/testing/product-images-' . Str::random(8));
        File::ensureDirectoryExists($this->imageDir);
        config(['backup.product_image_path' => $this->imageDir]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->imageDir);
        parent::tearDown();
    }

    private function asAdmin(): static
    {
        return $this->withSession(['CustomerID' => $this->admin->CustomerID]);
    }

    private function productPayload(array $overrides = []): array
    {
        return array_replace([
            'ProductName' => 'Áo Polo Thử Nghiệm',
            'Description' => 'Mô tả sản phẩm',
            'BasePrice' => 350000,
            'DiscountPercent' => 10,
            'CategoryID' => 1,
            'IsActive' => 'True',
            'MainImage' => UploadedFile::fake()->image('main.jpg', 600, 800),
            'AdditionalImages' => [UploadedFile::fake()->image('a.png'), UploadedFile::fake()->image('b.webp')],
            'Variants' => [
                ['ColorID' => 1, 'SizeID' => 1, 'SKU' => '', 'Stock' => 10, 'Status' => 'Available'],
                ['ColorID' => 1, 'SizeID' => 2, 'SKU' => 'POLO-CUSTOM', 'Stock' => 5, 'Status' => 'OutOfStock'],
            ],
        ], $overrides);
    }

    // ===== Sản phẩm =====

    public function test_product_index_filters_and_pagination(): void
    {
        $page1 = $this->asAdmin()->get('/Admin/Product/Index')->assertOk()->assertSee('Quản lý Sản phẩm');
        $this->assertCount(10, $page1->viewData('products'));
        $this->assertSame(20, $page1->viewData('products')->total());
        $page1->assertSee('/Admin/Product/Index?page=2', false);

        $this->asAdmin()->get('/Admin/Product/Index?page=2')->assertOk();

        $filtered = $this->asAdmin()->get('/Admin/Product/Index?search=jean&categoryId=&status=true')->assertOk();
        foreach ($filtered->viewData('products') as $product) {
            $this->assertStringContainsStringIgnoringCase('jean', $product->ProductName);
            $this->assertTrue($product->IsActive);
        }

        $withStock = $page1->viewData('products')->first();
        $this->assertSame((int) ProductVariant::where('ProductID', $withStock->ProductID)->sum('Stock'), (int) $withStock->total_stock);
    }

    public function test_staff_can_manage_products(): void
    {
        $staff = Customer::where('UserName', 'nguyenvana')->first();
        $staff->update(['RoleID' => 2]);

        $this->withSession(['CustomerID' => $staff->CustomerID])->get('/Admin/Product/Index')->assertOk();
    }

    public function test_create_product_with_variants_and_images(): void
    {
        $this->asAdmin()->get('/Admin/Product/Create')->assertOk()->assertSee('Thêm sản phẩm mới');

        $this->asAdmin()->post('/Admin/Product/Create', $this->productPayload())
            ->assertRedirect(route('admin.product.index'))
            ->assertSessionHas('Success');

        $product = Product::with(['productVariants', 'productImages'])->where('ProductName', 'Áo Polo Thử Nghiệm')->firstOrFail();
        $this->assertTrue($product->IsActive);
        $this->assertEquals(10, $product->DiscountPercent);
        $this->assertCount(2, $product->productVariants);
        $this->assertSame(sprintf('P%04d-0101', $product->ProductID), $product->productVariants->firstWhere('SizeID', 1)->SKU);
        $this->assertSame('POLO-CUSTOM', $product->productVariants->firstWhere('SizeID', 2)->SKU);
        $this->assertSame('OutOfStock', $product->productVariants->firstWhere('SizeID', 2)->Status);

        $this->assertCount(3, $product->productImages);
        $primary = $product->productImages->firstWhere('IsPrimary', true);
        $this->assertMatchesRegularExpression('/^p\d{4}_[a-z0-9]{12}\.jpg$/', $primary->ImageUrl);
        foreach ($product->productImages as $image) {
            $this->assertFileExists($this->imageDir . '/' . $image->ImageUrl);
        }

        // Sản phẩm mới hiện ở trang khách hàng
        $this->get('/Product/Details/' . $product->ProductID)->assertOk()->assertSee('Áo Polo Thử Nghiệm');
    }

    public function test_create_product_validation(): void
    {
        $count = Product::count();

        $this->asAdmin()->from('/Admin/Product/Create')
            ->post('/Admin/Product/Create', $this->productPayload(['Variants' => [], 'ProductName' => '', 'CategoryID' => 9999]))
            ->assertRedirect('/Admin/Product/Create')
            ->assertSessionHasErrors(['ProductName', 'CategoryID', 'Variants']);

        $this->asAdmin()->post('/Admin/Product/Create', $this->productPayload([
            'MainImage' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ]))->assertSessionHasErrors('MainImage');

        $this->asAdmin()->post('/Admin/Product/Create', $this->productPayload([
            'Variants' => [
                ['ColorID' => 1, 'SizeID' => 1, 'Stock' => 1],
                ['ColorID' => 1, 'SizeID' => 1, 'Stock' => 2],
            ],
        ]))->assertSessionHasErrors('Variants');

        $existingSku = ProductVariant::value('SKU');
        $this->asAdmin()->post('/Admin/Product/Create', $this->productPayload([
            'Variants' => [['ColorID' => 1, 'SizeID' => 1, 'SKU' => $existingSku, 'Stock' => 1]],
        ]))->assertSessionHasErrors('Variants');

        $this->asAdmin()->post('/Admin/Product/Create', $this->productPayload([
            'AdditionalImages' => array_map(fn ($i) => UploadedFile::fake()->image("x{$i}.jpg"), range(1, 5)),
        ]))->assertSessionHasErrors('AdditionalImages');

        $this->assertSame($count, Product::count());
        $this->assertSame([], File::files($this->imageDir));
    }

    public function test_edit_product_updates_fields_variants_and_main_image(): void
    {
        $product = Product::with('productVariants')->find(1);
        $variant = $product->productVariants->first();
        $oldPrimaryName = 'p0001_oldprimary00.jpg';
        File::put($this->imageDir . '/' . $oldPrimaryName, 'x');
        ProductImage::where('ProductID', 1)->where('IsPrimary', true)->update(['ImageUrl' => $oldPrimaryName]);

        $this->asAdmin()->get('/Admin/Product/Edit/1')->assertOk()->assertSee($product->ProductName)->assertSee($variant->SKU);

        $variants = $product->productVariants->values()->map(fn ($v) => [
            'VariantID' => $v->VariantID, 'SKU' => $v->SKU, 'Stock' => $v->Stock, 'Status' => $v->Status,
        ])->all();
        $variants[0] = ['VariantID' => $variant->VariantID, 'SKU' => '', 'Stock' => 77, 'Status' => 'OutOfStock'];

        $this->asAdmin()->post('/Admin/Product/Edit/1', [
            'ProductName' => 'Tên mới',
            'Description' => 'Mô tả mới',
            'BasePrice' => 123000,
            'DiscountPercent' => 5,
            'CategoryID' => 2,
            'IsActive' => 'False',
            'MainImage' => UploadedFile::fake()->image('new.png'),
            'Variants' => $variants,
        ])->assertRedirect(route('admin.product.index'))->assertSessionHas('Success', 'Cập nhật sản phẩm thành công.');

        $product->refresh();
        $this->assertSame('Tên mới', $product->ProductName);
        $this->assertFalse($product->IsActive);
        $this->assertSame(2, (int) $product->CategoryID);
        $variant->refresh();
        $this->assertSame(77, $variant->Stock);
        $this->assertSame('OutOfStock', $variant->Status);
        $this->assertSame(sprintf('P%04d-%02d%02d', 1, $variant->ColorID, $variant->SizeID), $variant->SKU);

        $primary = ProductImage::where('ProductID', 1)->where('IsPrimary', true)->first();
        $this->assertStringEndsWith('.png', $primary->ImageUrl);
        $this->assertFileExists($this->imageDir . '/' . $primary->ImageUrl);
        $this->assertFileDoesNotExist($this->imageDir . '/' . $oldPrimaryName);
    }

    public function test_edit_rejects_variants_of_other_products(): void
    {
        $otherVariant = ProductVariant::where('ProductID', 2)->first();

        $this->asAdmin()->post('/Admin/Product/Edit/1', [
            'ProductName' => 'X', 'BasePrice' => 1, 'CategoryID' => 1, 'IsActive' => 'True',
            'Variants' => [['VariantID' => $otherVariant->VariantID, 'Stock' => 0, 'Status' => 'Available']],
        ])->assertSessionHasErrors('Variants');

        $this->assertNotSame(0, $otherVariant->fresh()->Stock === 0 ? 1 : $otherVariant->fresh()->Stock);
        $this->asAdmin()->get('/Admin/Product/Edit/999999')->assertNotFound();
    }

    public function test_status_toggle_and_bulk_status(): void
    {
        $this->asAdmin()->from('/Admin/Product/Index')->post('/Admin/Product/EditStatus', ['productId' => 1, 'status' => 'false'])
            ->assertRedirect('/Admin/Product/Index');
        $this->assertFalse(Product::find(1)->IsActive);

        $this->asAdmin()->from('/Admin/Product/Index')->post('/Admin/Product/EditManyStatus', ['productId' => '1,2,3', 'status' => 'true'])
            ->assertSessionHas('Success', 'Đã cập nhật trạng thái 3 sản phẩm.');
        $this->assertSame(3, Product::whereIn('ProductID', [1, 2, 3])->where('IsActive', true)->count());

        $this->asAdmin()->from('/Admin/Product/Index')->post('/Admin/Product/EditManyStatus', ['productId' => 'abc', 'status' => 'true'])
            ->assertSessionHas('Error');
    }

    public function test_edit_images_add_limit_and_delete(): void
    {
        $product = Product::with('productImages')->find(1);
        $existingAdditional = $product->productImages->where('IsPrimary', false)->count();
        $remaining = 4 - $existingAdditional;

        $this->asAdmin()->get('/Admin/Product/EditImages/1')->assertOk()->assertSee("({$existingAdditional}/4 ảnh)");

        $this->asAdmin()->post('/Admin/Product/EditImages/1', [
            'AdditionalImages' => array_map(fn ($i) => UploadedFile::fake()->image("n{$i}.jpg"), range(1, $remaining + 1)),
        ])->assertSessionHasErrors('AdditionalImages');

        $this->asAdmin()->post('/Admin/Product/EditImages/1', [
            'AdditionalImages' => array_map(fn ($i) => UploadedFile::fake()->image("n{$i}.jpg"), range(1, $remaining)),
        ])->assertRedirect(route('admin.product.edit-images', 1))->assertSessionHas('Success');
        $this->assertSame(4, ProductImage::where('ProductID', 1)->where('IsPrimary', false)->count());

        $this->asAdmin()->post('/Admin/Product/EditImages/1', [
            'AdditionalImages' => [UploadedFile::fake()->image('over.jpg')],
        ])->assertSessionHasErrors(['AdditionalImages' => 'Không thể thêm ảnh vì đã đủ số lượng ảnh phụ (4 ảnh).']);

        $newImage = ProductImage::where('ProductID', 1)->where('IsPrimary', false)->latest('ImageID')->first();
        $this->asAdmin()->post('/Admin/Product/DeleteImage', ['productId' => 1, 'imageId' => $newImage->ImageID])
            ->assertSessionHas('Success', 'Đã xóa ảnh.');
        $this->assertNull(ProductImage::find($newImage->ImageID));
        $this->assertFileDoesNotExist($this->imageDir . '/' . $newImage->ImageUrl);

        $primary = ProductImage::where('ProductID', 1)->where('IsPrimary', true)->first();
        $this->asAdmin()->post('/Admin/Product/DeleteImage', ['productId' => 1, 'imageId' => $primary->ImageID])
            ->assertSessionHas('Error');
        $otherImage = ProductImage::where('ProductID', 2)->first();
        $this->asAdmin()->post('/Admin/Product/DeleteImage', ['productId' => 1, 'imageId' => $otherImage->ImageID])
            ->assertSessionHas('Error', 'Ảnh không tồn tại.');
        $this->assertNotNull($otherImage->fresh());
    }

    public function test_delete_product_rules(): void
    {
        // Sản phẩm đã có trong đơn hàng: không xóa
        $ordered = ProductVariant::whereHas('orderDetails')->first()->ProductID;
        $this->asAdmin()->post('/Admin/Product/Delete', ['id' => $ordered])->assertSessionHas('Error');
        $this->assertNotNull(Product::find($ordered));

        // Sản phẩm mới chưa bán: xóa được, kèm biến thể, ảnh, dòng giỏ hàng
        $this->asAdmin()->post('/Admin/Product/Create', $this->productPayload());
        $product = Product::with('productVariants', 'productImages')->where('ProductName', 'Áo Polo Thử Nghiệm')->first();
        $cart = Cart::create(['CartToken' => (string) Str::uuid()]);
        CartItem::create(['CartID' => $cart->CartID, 'VariantID' => $product->productVariants->first()->VariantID, 'Quantity' => 1]);
        $files = $product->productImages->pluck('ImageUrl')->all();

        $this->asAdmin()->post('/Admin/Product/Delete', ['id' => $product->ProductID])
            ->assertSessionHas('Success', "Đã xóa sản phẩm \"Áo Polo Thử Nghiệm\".");
        $this->assertNull(Product::find($product->ProductID));
        $this->assertSame(0, ProductVariant::where('ProductID', $product->ProductID)->count());
        $this->assertSame(0, CartItem::where('CartID', $cart->CartID)->count());
        foreach ($files as $file) {
            $this->assertFileDoesNotExist($this->imageDir . '/' . $file);
        }

        $this->asAdmin()->post('/Admin/Product/DeleteMany', ['id' => "{$ordered},999999"])
            ->assertSessionHas('Error');
    }

    // ===== Đơn hàng =====

    public function test_order_index_filters_and_counts(): void
    {
        $response = $this->asAdmin()->get('/Admin/Order/Index')->assertOk()->assertSee('Quản lý Đơn hàng');
        $this->assertCount(10, $response->viewData('orders'));
        $this->assertSame(Order::where('Status', 'Completed')->count(), (int) $response->viewData('statusCounts')['Completed']);

        $byStatus = $this->asAdmin()->get('/Admin/Order/Index?status=Pending')->assertOk();
        $this->assertSame(['Pending'], $byStatus->viewData('orders')->pluck('Status')->unique()->values()->all());

        $bySearch = $this->asAdmin()->get('/Admin/Order/Index?search=%235')->assertOk();
        $this->assertSame([5], $bySearch->viewData('orders')->pluck('OrderID')->all());

        $this->asAdmin()->get('/Admin/Order/Index?fromDate=2099-01-01')->assertOk()->assertSee('Không tìm thấy đơn hàng nào.');
        $this->asAdmin()->get('/Admin/Order/Index?fromDate=abc&search=xyz')->assertOk();

        // Mỗi dòng có sẵn ô chọn trạng thái, không cần JavaScript
        $pending = Order::where('Status', 'Pending')->value('OrderID');
        $this->asAdmin()->get('/Admin/Order/Index?status=Pending')
            ->assertSee(route('admin.order.edit-status', $pending), false)
            ->assertSee('<option value="Processing">Đang xử lý</option>', false);
    }

    public function test_order_details_page(): void
    {
        $order = Order::with('orderDetails')->where('Status', 'Pending')->first();

        // Form đổi trạng thái hiện sẵn trên trang, chỉ liệt kê các trạng thái được phép chuyển
        $this->asAdmin()->get('/Admin/Order/Details/' . $order->OrderID)->assertOk()
            ->assertSee("Chi tiết Đơn hàng #{$order->OrderID}")
            ->assertSee('Chờ xác nhận')
            ->assertSee('Chuyển sang trạng thái')
            ->assertSee('<option value="Processing">Đang xử lý</option>', false)
            ->assertSee('<option value="Cancelled">Đã hủy</option>', false)
            ->assertDontSee('<option value="Shipped">', false)
            ->assertSee('Hủy đơn hàng');

        $completed = Order::where('Status', 'Completed')->first();
        $this->asAdmin()->get('/Admin/Order/Details/' . $completed->OrderID)->assertOk()->assertDontSee('Chuyển sang trạng thái');

        $this->asAdmin()->get('/Admin/Order/Details/999999')->assertNotFound();
    }

    public function test_admin_changes_order_status_through_lifecycle(): void
    {
        $order = Order::with('orderDetails')->where('Status', 'Pending')->first();
        $detail = $order->orderDetails->first();
        $stockBefore = ProductVariant::find($detail->VariantID)->Stock;

        $this->asAdmin()->post("/Admin/Order/EditStatus/{$order->OrderID}", ['status' => 'Shipped'])
            ->assertRedirect(route('admin.order.details', $order->OrderID))
            ->assertSessionHas('Error', "Không thể chuyển đổi trạng thái từ 'Chờ xác nhận' sang 'Đang giao'.");

        foreach (['Processing', 'Shipped', 'Completed'] as $next) {
            $this->asAdmin()->post('/Admin/Order/EditStatus', ['id' => $order->OrderID, 'status' => $next, 'note' => "Chuyển {$next}"])
                ->assertSessionHas('Success', 'Cập nhật trạng thái đơn hàng thành công.');
        }

        $this->assertSame('Completed', $order->fresh()->Status);
        $this->assertSame($stockBefore, ProductVariant::find($detail->VariantID)->Stock);
        $this->assertSame(3, OrderStatusHistory::where('OrderID', $order->OrderID)->where('ChangedBy', 'admin')->count());
        $this->assertDatabaseHas('OrderStatusHistory', ['OrderID' => $order->OrderID, 'OldStatus' => 'Shipped', 'NewStatus' => 'Completed', 'Note' => 'Chuyển Completed']);

        $this->asAdmin()->get('/Admin/Order/Details/' . $order->OrderID)->assertSee('Chuyển Completed')->assertSee('bởi <strong>admin</strong>', false);

        $this->asAdmin()->post('/Admin/Order/EditStatus', ['id' => $order->OrderID, 'status' => 'Cancelled'])->assertSessionHas('Error');
        $this->asAdmin()->post('/Admin/Order/EditStatus', ['id' => $order->OrderID, 'status' => 'Bogus'])->assertSessionHas('Error', 'Trạng thái không hợp lệ.');
    }

    public function test_admin_cancel_restores_stock_once(): void
    {
        $order = Order::with('orderDetails')->where('Status', 'Processing')->first();
        $detail = $order->orderDetails->first();
        $stockBefore = ProductVariant::find($detail->VariantID)->Stock;
        $qtyForVariant = $order->orderDetails->where('VariantID', $detail->VariantID)->sum('Quantity');

        $this->asAdmin()->post("/Admin/Order/EditStatus/{$order->OrderID}", ['status' => 'Cancelled'])->assertSessionHas('Success');
        $this->asAdmin()->post("/Admin/Order/EditStatus/{$order->OrderID}", ['status' => 'Cancelled'])->assertSessionHas('Error');

        $this->assertSame('Cancelled', $order->fresh()->Status);
        $this->assertSame($stockBefore + $qtyForVariant, ProductVariant::find($detail->VariantID)->Stock);
    }

    public function test_bulk_order_status(): void
    {
        $pending = Order::where('Status', 'Pending')->take(2)->pluck('OrderID');
        $completed = Order::where('Status', 'Completed')->value('OrderID');

        $this->asAdmin()->post('/Admin/Order/EditManyStatus', ['id' => $pending->push($completed)->implode(','), 'status' => 'Processing'])
            ->assertRedirect(route('admin.order.index'))
            ->assertSessionHas('Success', 'Đã cập nhật 2 đơn hàng.')
            ->assertSessionHas('Error');

        $this->assertSame(2, Order::whereIn('OrderID', $pending->take(2))->where('Status', 'Processing')->count());
        $this->assertSame('Completed', Order::find($completed)->Status);
    }
}
