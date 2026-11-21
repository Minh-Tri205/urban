<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPagesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_home_page_shows_six_active_products(): void
    {
        Product::whereKey(1)->update(['IsActive' => false]);

        $response = $this->get('/')->assertOk()
            ->assertSee('Chào mừng đến với URBAN')
            ->assertSee('Content/img/banner-image1.jpg', false);

        $this->assertCount(6, $response->viewData('products'));
        $this->assertNotContains(1, $response->viewData('products')->pluck('ProductID')->all());
    }

    public function test_about_page(): void
    {
        $this->get('/Home/About')->assertOk()->assertSee('About Us');
    }

    public function test_error_not_found_route(): void
    {
        $this->get('/Error/NotFound')->assertNotFound()->assertSee('Error 404');
    }

    public function test_product_index_paginates_nine_per_page(): void
    {
        $total = Product::where('IsActive', true)->count();

        $page1 = $this->get('/Product/Index')->assertOk();
        $this->assertCount(9, $page1->viewData('products'));
        $page1->assertSee("Hiển thị 1–9 trong số {$total} sản phẩm")
            ->assertSee('Thời trang Nam');

        $page3 = $this->get('/Product/Index?page=3')->assertOk();
        $this->assertCount($total - 18, $page3->viewData('products'));
        $page3->assertSee("Hiển thị 19–{$total} trong số {$total} sản phẩm");
    }

    public function test_product_index_filters_by_category_keyword_price_and_sort(): void
    {
        $category = Category::has('products')->first();

        // catID và catId đều nhận được (ASP.NET không phân biệt hoa thường)
        foreach (['catID', 'catId'] as $param) {
            $response = $this->get("/Product/Index?{$param}={$category->CategoryID}")->assertOk();
            $ids = $response->viewData('products')->pluck('CategoryID')->unique()->values()->all();
            $this->assertSame([$category->CategoryID], array_map('intval', $ids));
            $response->assertSee($category->CategoryName);
        }

        $response = $this->get('/Product/Index?kw=' . urlencode('áo thun'))->assertOk();
        $this->assertNotEmpty($response->viewData('products'));
        foreach ($response->viewData('products') as $product) {
            $this->assertStringContainsStringIgnoringCase('áo thun', mb_strtolower($product->ProductName));
        }

        $response = $this->get('/Product/Index?price=2&sort=4')->assertOk();
        $prices = $response->viewData('products')->pluck('BasePrice')->map(fn ($p) => (float) $p)->all();
        $this->assertNotEmpty($prices);
        foreach ($prices as $price) {
            $this->assertTrue($price >= 300000 && $price < 500000);
        }
        $sorted = $prices;
        rsort($sorted);
        $this->assertSame($sorted, $prices);

        // Link phân trang giữ lại bộ lọc
        $response = $this->get('/Product/Index?sort=3')->assertOk();
        $response->assertSee(e(route('product.index', ['sort' => 3, 'page' => 2])), false);

        $this->get('/Product/Index?kw=khongcosanphamnay')->assertOk()->assertSee('Không tìm thấy sản phẩm nào');
        $this->get('/Product/Index?kw=%25')->assertOk()->assertSee('Không tìm thấy sản phẩm nào');
        $this->get('/Product/Index?page=-5&sort=abc&catID=xyz')->assertOk();
    }

    public function test_product_details_page(): void
    {
        $product = Product::with('productVariants.color', 'productVariants.size', 'category')->find(1);

        $response = $this->get('/Product/Details/1')->assertOk()
            ->assertSee($product->ProductName)
            ->assertSee(vnd($product->BasePrice))
            ->assertSee($product->category->CategoryName)
            ->assertSee('Thêm vào giỏ')
            ->assertSee('name="_token"', false)
            ->assertSee(route('cart.add'), false)
            ->assertSee('Sản phẩm liên quan');

        $colorCount = $product->productVariants->pluck('ColorID')->unique()->count();
        $this->assertCount($colorCount, $response->viewData('colors'));
        $this->assertNotContains(1, $response->viewData('relatedProducts')->pluck('ProductID')->all());
    }

    public function test_inactive_product_details_shows_out_of_stock(): void
    {
        Product::whereKey(1)->update(['IsActive' => false]);

        $this->get('/Product/Details/1')->assertOk()
            ->assertSee('Hết hàng')
            ->assertDontSee('Thêm vào giỏ');
    }

    public function test_product_details_not_found(): void
    {
        $this->get('/Product/Details/999999')->assertNotFound()->assertSee('Error 404');
        $this->get('/Product/Details/abc')->assertNotFound();
        $this->get('/Product/Details')->assertNotFound();
    }
}
