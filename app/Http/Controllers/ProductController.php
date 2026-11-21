<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public const PAGE_SIZE = 9;

    public function index(Request $request)
    {
        // ASP.NET bind tham số không phân biệt hoa thường (catID / catId)
        $query = array_change_key_case($request->query(), CASE_LOWER);

        $catId = $this->intOrNull($query['catid'] ?? null);
        $kw = is_string($query['kw'] ?? null) ? trim($query['kw']) : '';
        $page = max(1, (int) ($query['page'] ?? 1));
        $sort = (int) ($query['sort'] ?? 0);
        $price = (int) ($query['price'] ?? 0);

        $products = Product::with('productImages')->where('IsActive', true);
        $categoryName = null;

        if ($catId !== null) {
            $products->where('CategoryID', $catId);
            $categoryName = Category::find($catId)?->CategoryName;
        }

        if ($kw !== '') {
            $products->where('ProductName', 'like', '%' . addcslashes($kw, '%_\\') . '%');
        }

        match ($price) {
            1 => $products->where('BasePrice', '<', 300000),
            2 => $products->where('BasePrice', '>=', 300000)->where('BasePrice', '<', 500000),
            3 => $products->where('BasePrice', '>=', 500000)->where('BasePrice', '<', 1000000),
            4 => $products->where('BasePrice', '>=', 1000000),
            default => null,
        };

        match ($sort) {
            1 => $products->orderBy('ProductName'),
            2 => $products->orderByDesc('ProductName'),
            3 => $products->orderBy('BasePrice'),
            4 => $products->orderByDesc('BasePrice'),
            default => null,
        };
        $products->orderBy('ProductID');

        $totalProducts = (clone $products)->count();
        $totalPages = (int) ceil($totalProducts / self::PAGE_SIZE);
        $productsOnPage = $products->skip(($page - 1) * self::PAGE_SIZE)->take(self::PAGE_SIZE)->get();

        // Giữ bộ lọc hiện tại khi chuyển trang / đổi khoảng giá
        $filters = array_filter(
            ['catID' => $catId, 'kw' => $kw, 'sort' => $sort, 'price' => $price],
            fn ($v) => $v !== null && $v !== '' && $v !== 0
        );

        return view('product.index', [
            'products' => $productsOnPage,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
            'firstIndex' => ($page - 1) * self::PAGE_SIZE + 1,
            'categoryName' => $categoryName,
            'catId' => $catId,
            'kw' => $kw,
            'sort' => $sort,
            'price' => $price,
            'filters' => $filters,
            'categoryGroups' => CategoryGroup::with('categories')->orderBy('GroupID')->get(),
        ]);
    }

    public function details(?string $id = null)
    {
        $product = ctype_digit((string) $id)
            ? Product::with([
                'productImages',
                'category.categoryGroup',
                'productVariants.color',
                'productVariants.size',
            ])->find($id)
            : null;

        abort_if($product === null, 404);

        $relatedProducts = Product::with('productImages')
            ->where('ProductID', '!=', $product->ProductID)
            ->where('CategoryID', $product->CategoryID)
            ->orderBy('ProductID')
            ->get();

        $colors = $product->productVariants->pluck('color')->filter()->unique('ColorID')->sortBy('ColorID');
        $sizes = $product->productVariants->pluck('size')->filter()->unique('SizeID')->sortBy('SizeID');

        return view('product.details', compact('product', 'relatedProducts', 'colors', 'sizes'));
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }
}
