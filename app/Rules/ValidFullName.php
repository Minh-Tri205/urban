<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Port từ Attributes/ValidFullNameAttribute.cs
 * - Độ dài từ 2 đến 100 ký tự
 * - Chỉ chứa chữ cái (có dấu), khoảng trắng, dấu '-' và '.'
 */
class ValidFullName implements ValidationRule
{
    public const PATTERN = '/^[\p{L}\s\-\.]+$/u';

    public static function passes(mixed $value): bool
    {
        if (! is_string($value) || trim($value) === '') {
            return false;
        }

        $fullName = trim($value);
        $length = mb_strlen($fullName);

        return $length >= 2 && $length <= 100 && preg_match(self::PATTERN, $fullName) === 1;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::passes($value)) {
            $fail("Họ và tên chỉ được chứa chữ cái, khoảng trắng, dấu '-' và '.' (2-100 ký tự)");
        }
    }
}
