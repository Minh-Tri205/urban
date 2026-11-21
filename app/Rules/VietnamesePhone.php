<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Port từ Attributes/VietnamesePhoneAttribute.cs
 * Bắt đầu bằng 0 hoặc +84, sau đó là 9-10 chữ số (chữ số đầu khác 0).
 */
class VietnamesePhone implements ValidationRule
{
    public const PATTERN = '/^(0|\+84)[1-9][0-9]{8,9}$/';

    public static function normalize(string $phone): string
    {
        // Loại bỏ khoảng trắng, dấu gạch ngang, dấu ngoặc
        return preg_replace('/[\s\-\(\)]/u', '', trim($phone));
    }

    public static function passes(mixed $value): bool
    {
        if (! is_string($value) || trim($value) === '') {
            return false;
        }

        return preg_match(self::PATTERN, self::normalize($value)) === 1;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::passes($value)) {
            $fail('Số điện thoại không hợp lệ.');
        }
    }
}
