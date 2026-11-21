<?php

namespace App\Services;

use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Lưu ảnh sản phẩm vào public/Content/img/products (cùng thư mục với bản gốc).
 * Bản gốc lưu theo tên file người dùng gửi lên nên ảnh trùng tên ghi đè nhau;
 * ở đây tên file được sinh ngẫu nhiên.
 */
class ProductImageStorage
{
    public const MAX_ADDITIONAL_IMAGES = 4;
    public const MAX_SIZE_KB = 5120;
    public const RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_SIZE_KB];

    public function directory(): string
    {
        return config('backup.product_image_path') ?? public_path('Content/img/products');
    }

    public function store(UploadedFile $file, int $productId): string
    {
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $fileName = sprintf('p%04d_%s.%s', $productId, Str::lower(Str::random(12)), $extension);

        $file->move($this->directory(), $fileName);

        return $fileName;
    }

    /** Xóa file ảnh nếu không còn bản ghi ProductImages nào dùng tới. */
    public function deleteIfUnused(?string $fileName): void
    {
        if ($fileName === null || $fileName === '' || $fileName === 'default.jpg' || basename($fileName) !== $fileName) {
            return;
        }

        if (ProductImage::where('ImageUrl', $fileName)->exists()) {
            return;
        }

        $path = $this->directory() . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
