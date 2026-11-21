<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Color;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Services\ProductImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductController extends Controller
{
    private const PAGE_SIZE = 10;
    private const VARIANT_STATUSES = ['Available', 'OutOfStock'];

    public function __construct(private ProductImageStorage $images)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->query('categoryId');
        $status = $request->query('status');

        $query = Product::with(['category', 'productImages'])
            ->withSum('productVariants as total_stock', 'Stock')
            ->when($search !== '', fn ($q) => $q->where('ProductName', 'like', '%' . addcslashes($search, '%_\\') . '%'))
            ->when(is_string($categoryId) && ctype_digit($categoryId), fn ($q) => $q->where('CategoryID', (int) $categoryId))
            ->when(in_array($status, ['true', 'false'], true), fn ($q) => $q->where('IsActive', $status === 'true'))
            ->orderByDesc('ProductID');

        $products = $query->paginate(self::PAGE_SIZE)->withQueryString();

        return view('admin.product.index', [
            'products' => $products,
            'categories' => Category::orderBy('CategoryName')->get(),
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('admin.product.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request, creating: true);

        $product = DB::transaction(function () use ($data, $request) {
            $product = Product::create($this->productAttributes($data) + ['CreatedAt' => now()]);

            foreach (array_values($data['Variants']) as $variant) {
                ProductVariant::create([
                    'ProductID' => $product->ProductID,
                    'ColorID' => (int) $variant['ColorID'],
                    'SizeID' => (int) $variant['SizeID'],
                    'SKU' => $this->skuFor($product->ProductID, $variant),
                    'Stock' => (int) $variant['Stock'],
                    'Status' => $variant['Status'] ?? 'Available',
                ]);
            }

            ProductImage::create([
                'ProductID' => $product->ProductID,
                'ImageUrl' => $this->images->store($request->file('MainImage'), $product->ProductID),
                'IsPrimary' => true,
            ]);

            foreach ($request->file('AdditionalImages', []) as $file) {
                ProductImage::create([
                    'ProductID' => $product->ProductID,
                    'ImageUrl' => $this->images->store($file, $product->ProductID),
                    'IsPrimary' => false,
                ]);
            }

            return $product;
        });

        return redirect()->route('admin.product.index')
            ->with('Success', "Đã thêm sản phẩm \"{$product->ProductName}\" với " . count($data['Variants']) . ' biến thể.');
    }

    public function edit(string $id)
    {
        $product = $this->findProduct($id, ['productImages', 'productVariants.color', 'productVariants.size']);

        return view('admin.product.edit', ['product' => $product] + $this->formOptions());
    }

    public function updateProduct(Request $request, string $id)
    {
        $product = $this->findProduct($id, ['productImages', 'productVariants']);
        $data = $this->validateProduct($request, creating: false, product: $product);

        $oldPrimary = null;

        DB::transaction(function () use ($product, $data, $request, &$oldPrimary) {
            $product->update($this->productAttributes($data));

            foreach ($data['Variants'] ?? [] as $input) {
                $variant = $product->productVariants->firstWhere('VariantID', (int) ($input['VariantID'] ?? 0));

                if ($variant) {
                    $variant->update([
                        'Stock' => (int) $input['Stock'],
                        'Status' => $input['Status'] ?? 'Available',
                        'SKU' => $this->skuFor($product->ProductID, ['ColorID' => $variant->ColorID, 'SizeID' => $variant->SizeID] + $input),
                    ]);
                }
            }

            if ($request->hasFile('MainImage')) {
                $oldPrimary = $this->replacePrimaryImage($product, $request->file('MainImage'));
            }
        });

        $this->images->deleteIfUnused($oldPrimary);

        return redirect()->route('admin.product.index')->with('Success', 'Cập nhật sản phẩm thành công.');
    }

    public function editStatus(Request $request)
    {
        $product = Product::find((int) $request->input('productId'));
        $status = $this->parseBool($request->input('status'));

        if ($product === null || $status === null) {
            return back()->with('Error', 'Dữ liệu không hợp lệ.');
        }

        $product->update(['IsActive' => $status]);

        return back()->with('Success', "Đã chuyển \"{$product->ProductName}\" sang " . ($status ? 'Còn hàng.' : 'Hết hàng.'));
    }

    public function editManyStatus(Request $request)
    {
        $ids = $this->parseIds($request->input('productId'));
        $status = $this->parseBool($request->input('status'));

        if ($ids === [] || $status === null) {
            return back()->with('Error', 'Vui lòng chọn ít nhất một sản phẩm.');
        }

        $count = Product::whereIn('ProductID', $ids)->count();
        Product::whereIn('ProductID', $ids)->update(['IsActive' => $status]);

        return back()->with('Success', "Đã cập nhật trạng thái {$count} sản phẩm.");
    }

    public function editImages(string $id)
    {
        $product = $this->findProduct($id, ['productImages']);

        return view('admin.product.edit-images', [
            'product' => $product,
            'maxAdditional' => ProductImageStorage::MAX_ADDITIONAL_IMAGES,
        ]);
    }

    public function updateImages(Request $request, string $id)
    {
        $product = $this->findProduct($id, ['productImages']);
        $additionalCount = $product->productImages->where('IsPrimary', false)->count();
        $remaining = max(0, ProductImageStorage::MAX_ADDITIONAL_IMAGES - $additionalCount);

        $request->validate([
            'MainImage' => ['nullable', ...ProductImageStorage::RULES],
            'AdditionalImages' => ['nullable', 'array', 'max:' . $remaining],
            'AdditionalImages.*' => ProductImageStorage::RULES,
        ], $this->imageMessages() + [
            'AdditionalImages.max' => $remaining === 0
                ? 'Không thể thêm ảnh vì đã đủ số lượng ảnh phụ (' . ProductImageStorage::MAX_ADDITIONAL_IMAGES . ' ảnh).'
                : "Chỉ được thêm tối đa {$remaining} ảnh phụ.",
        ]);

        if (! $request->hasFile('MainImage') && ! $request->hasFile('AdditionalImages')) {
            return back()->with('Error', 'Vui lòng chọn ảnh cần tải lên.');
        }

        $oldPrimary = null;

        DB::transaction(function () use ($product, $request, &$oldPrimary) {
            if ($request->hasFile('MainImage')) {
                $oldPrimary = $this->replacePrimaryImage($product, $request->file('MainImage'));
            }

            foreach ($request->file('AdditionalImages', []) as $file) {
                ProductImage::create([
                    'ProductID' => $product->ProductID,
                    'ImageUrl' => $this->images->store($file, $product->ProductID),
                    'IsPrimary' => false,
                ]);
            }
        });

        $this->images->deleteIfUnused($oldPrimary);

        return redirect()->route('admin.product.edit-images', $product->ProductID)->with('Success', 'Cập nhật hình ảnh thành công.');
    }

    public function deleteImage(Request $request)
    {
        $productId = (int) $request->input('productId');
        $image = ProductImage::where('ProductID', $productId)->find((int) $request->input('imageId'));

        if ($image === null) {
            return redirect()->route('admin.product.edit-images', $productId)->with('Error', 'Ảnh không tồn tại.');
        }

        if ($image->IsPrimary) {
            return redirect()->route('admin.product.edit-images', $productId)->with('Error', 'Không thể xóa ảnh chính. Hãy tải ảnh chính mới để thay thế.');
        }

        $image->delete();
        $this->images->deleteIfUnused($image->ImageUrl);

        return redirect()->route('admin.product.edit-images', $productId)->with('Success', 'Đã xóa ảnh.');
    }

    public function delete(Request $request, ?string $id = null)
    {
        $product = Product::with('productImages')->find((int) ($id ?? $request->input('id')));

        if ($product === null) {
            return redirect()->route('admin.product.index')->with('Error', 'Sản phẩm không tồn tại.');
        }

        if (! $this->deleteProduct($product)) {
            return redirect()->route('admin.product.index')
                ->with('Error', "Không thể xóa \"{$product->ProductName}\" vì đã có đơn hàng. Hãy chuyển sang trạng thái Hết hàng.");
        }

        return redirect()->route('admin.product.index')->with('Success', "Đã xóa sản phẩm \"{$product->ProductName}\".");
    }

    public function deleteMany(Request $request)
    {
        $ids = $this->parseIds($request->input('id'));

        if ($ids === []) {
            return redirect()->route('admin.product.index')->with('Error', 'Vui lòng chọn ít nhất một sản phẩm.');
        }

        $deleted = 0;
        $blocked = [];

        foreach (Product::with('productImages')->whereIn('ProductID', $ids)->get() as $product) {
            if ($this->deleteProduct($product)) {
                $deleted++;
            } else {
                $blocked[] = $product->ProductName;
            }
        }

        $redirect = redirect()->route('admin.product.index');

        if ($blocked !== []) {
            $redirect->with('Error', "Đã xóa {$deleted} sản phẩm. Không thể xóa vì đã có đơn hàng: " . implode(', ', $blocked) . '.');
        } else {
            $redirect->with('Success', "Đã xóa {$deleted} sản phẩm.");
        }

        return $redirect;
    }

    /** Sản phẩm đã có trong đơn hàng thì giữ lại để không mất lịch sử đơn. */
    private function deleteProduct(Product $product): bool
    {
        $variantIds = ProductVariant::where('ProductID', $product->ProductID)->pluck('VariantID');

        if (OrderDetail::whereIn('VariantID', $variantIds)->exists()) {
            return false;
        }

        $fileNames = $product->productImages->pluck('ImageUrl')->all();

        DB::transaction(function () use ($product, $variantIds) {
            CartItem::whereIn('VariantID', $variantIds)->delete();
            $product->delete(); // ProductVariants và ProductImages xóa theo khóa ngoại cascade
        });

        foreach ($fileNames as $fileName) {
            $this->images->deleteIfUnused($fileName);
        }

        return true;
    }

    /** Thay ảnh chính, trả về tên file cũ để xóa sau khi transaction thành công. */
    private function replacePrimaryImage(Product $product, $file): ?string
    {
        $fileName = $this->images->store($file, $product->ProductID);
        $primary = $product->productImages->firstWhere('IsPrimary', true);

        if ($primary) {
            $old = $primary->ImageUrl;
            $primary->update(['ImageUrl' => $fileName]);

            return $old;
        }

        ProductImage::create(['ProductID' => $product->ProductID, 'ImageUrl' => $fileName, 'IsPrimary' => true]);

        return null;
    }

    private function validateProduct(Request $request, bool $creating, ?Product $product = null): array
    {
        $rules = [
            'ProductName' => ['required', 'string', 'max:200'],
            'Description' => ['nullable', 'string', 'max:10000'],
            'BasePrice' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'DiscountPercent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'CategoryID' => ['required', 'integer', 'exists:Categories,CategoryID'],
            'IsActive' => ['required', 'in:True,False,true,false,1,0'],
            'MainImage' => [$creating ? 'required' : 'nullable', ...ProductImageStorage::RULES],
            'Variants' => [$creating ? 'required' : 'nullable', 'array', $creating ? 'min:1' : 'min:0'],
            'Variants.*.Stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'Variants.*.Status' => ['nullable', Rule::in(self::VARIANT_STATUSES)],
            'Variants.*.SKU' => ['nullable', 'string', 'max:50', 'distinct:ignore_case'],
        ];

        if ($creating) {
            $rules += [
                'Variants.*.ColorID' => ['required', 'integer', 'exists:Colors,ColorID'],
                'Variants.*.SizeID' => ['required', 'integer', 'exists:Sizes,SizeID'],
                'AdditionalImages' => ['nullable', 'array', 'max:' . ProductImageStorage::MAX_ADDITIONAL_IMAGES],
                'AdditionalImages.*' => ProductImageStorage::RULES,
            ];
        } else {
            $rules['Variants.*.VariantID'] = ['required', 'integer'];
        }

        $validator = validator($request->all(), $rules, $this->imageMessages() + [
            'ProductName.required' => 'Tên sản phẩm không được để trống.',
            'ProductName.max' => 'Tên sản phẩm không được vượt quá 200 ký tự.',
            'BasePrice.required' => 'Giá bán không được để trống.',
            'BasePrice.*' => 'Giá bán không hợp lệ.',
            'DiscountPercent.*' => '% giảm giá phải từ 0 đến 100.',
            'CategoryID.*' => 'Vui lòng chọn danh mục hợp lệ.',
            'IsActive.*' => 'Trạng thái sản phẩm không hợp lệ.',
            'MainImage.required' => 'Vui lòng chọn ảnh chính.',
            'Variants.required' => 'Vui lòng thêm ít nhất một biến thể sản phẩm.',
            'Variants.min' => 'Vui lòng thêm ít nhất một biến thể sản phẩm.',
            'Variants.*.Stock.*' => 'Tồn kho của biến thể phải là số nguyên từ 0.',
            'Variants.*.Status.*' => 'Trạng thái biến thể không hợp lệ.',
            'Variants.*.SKU.distinct' => 'SKU của các biến thể không được trùng nhau.',
            'Variants.*.SKU.max' => 'SKU không được vượt quá 50 ký tự.',
            'Variants.*.ColorID.*' => 'Màu sắc của biến thể không hợp lệ.',
            'Variants.*.SizeID.*' => 'Kích thước của biến thể không hợp lệ.',
            'AdditionalImages.max' => 'Chỉ được tải tối đa ' . ProductImageStorage::MAX_ADDITIONAL_IMAGES . ' ảnh phụ.',
        ]);

        $validator->after(function (Validator $validator) use ($request, $creating, $product) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $variants = collect($request->input('Variants', []));

            if ($creating) {
                $pairs = $variants->map(fn ($v) => $v['ColorID'] . '-' . $v['SizeID']);
                if ($pairs->duplicates()->isNotEmpty()) {
                    $validator->errors()->add('Variants', 'Có biến thể trùng màu sắc và kích thước.');
                }
            } else {
                $ownIds = $product->productVariants->pluck('VariantID')->all();
                if ($variants->contains(fn ($v) => ! in_array((int) $v['VariantID'], $ownIds, true))) {
                    $validator->errors()->add('Variants', 'Biến thể không thuộc sản phẩm này.');
                }
            }

            $skus = $variants->pluck('SKU')->map(fn ($s) => trim((string) $s))->filter()->values();
            if ($skus->isNotEmpty()) {
                $taken = ProductVariant::whereIn('SKU', $skus)
                    ->when(! $creating, fn ($q) => $q->where('ProductID', '!=', $product->ProductID))
                    ->pluck('SKU');
                if ($taken->isNotEmpty()) {
                    $validator->errors()->add('Variants', 'SKU đã tồn tại ở sản phẩm khác: ' . $taken->implode(', '));
                }
            }
        });

        return $validator->validate();
    }

    private function productAttributes(array $data): array
    {
        return [
            'ProductName' => trim($data['ProductName']),
            'Description' => $data['Description'] ?? null,
            'BasePrice' => $data['BasePrice'],
            'DiscountPercent' => $data['DiscountPercent'] ?? 0,
            'CategoryID' => (int) $data['CategoryID'],
            'IsActive' => $this->parseBool($data['IsActive']),
        ];
    }

    /** SKU để trống thì tự tạo theo mẫu P{ProductID:4}-{ColorID:2}{SizeID:2} như bản gốc. */
    private function skuFor(int $productId, array $variant): string
    {
        $sku = trim((string) ($variant['SKU'] ?? ''));

        return $sku !== '' ? $sku : sprintf('P%04d-%02d%02d', $productId, $variant['ColorID'], $variant['SizeID']);
    }

    private function formOptions(): array
    {
        return [
            'categories' => Category::with('categoryGroup')->orderBy('GroupID')->orderBy('CategoryName')->get(),
            'colors' => Color::orderBy('ColorID')->get(),
            'sizes' => Size::orderBy('SizeID')->get(),
        ];
    }

    private function findProduct(string $id, array $with = []): Product
    {
        $product = ctype_digit($id) ? Product::with($with)->find((int) $id) : null;
        abort_if($product === null, 404);

        return $product;
    }

    private function parseBool(mixed $value): ?bool
    {
        return match (strtolower((string) $value)) {
            'true', '1' => true,
            'false', '0' => false,
            default => null,
        };
    }

    /** @return list<int> */
    private function parseIds(mixed $value): array
    {
        $parts = is_array($value) ? $value : explode(',', (string) $value);

        return array_values(array_unique(array_map('intval', array_filter($parts, fn ($p) => ctype_digit(trim((string) $p))))));
    }

    private function imageMessages(): array
    {
        return [
            'MainImage.image' => 'Ảnh chính phải là file ảnh.',
            'MainImage.mimes' => 'Ảnh chính phải có định dạng JPG, PNG hoặc WEBP.',
            'MainImage.max' => 'Ảnh chính không được vượt quá 5MB.',
            'MainImage.uploaded' => 'Tải ảnh chính thất bại (có thể do file quá lớn).',
            'AdditionalImages.*.image' => 'Ảnh phụ phải là file ảnh.',
            'AdditionalImages.*.mimes' => 'Ảnh phụ phải có định dạng JPG, PNG hoặc WEBP.',
            'AdditionalImages.*.max' => 'Mỗi ảnh phụ không được vượt quá 5MB.',
            'AdditionalImages.*.uploaded' => 'Tải ảnh phụ thất bại (có thể do file quá lớn).',
        ];
    }
}
