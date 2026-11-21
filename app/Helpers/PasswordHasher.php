<?php

namespace App\Helpers;

/**
 * Port từ FashionStore/Helpers/PasswordHasher.cs.
 * Định dạng lưu: base64( salt[16 byte] + HMACSHA256(key = salt, data = password) )
 * Giữ nguyên thuật toán để các tài khoản tạo từ bản C# vẫn đăng nhập được.
 */
class PasswordHasher
{
    private const SALT_LENGTH = 16;

    public static function hashPassword(string $password): string
    {
        $salt = random_bytes(self::SALT_LENGTH);
        $hash = hash_hmac('sha256', $password, $salt, true);

        return base64_encode($salt . $hash);
    }

    public static function verifyPassword(string $password, ?string $stored): bool
    {
        $data = base64_decode((string) $stored, true);

        if ($data === false || strlen($data) <= self::SALT_LENGTH) {
            return false;
        }

        $salt = substr($data, 0, self::SALT_LENGTH);
        $storedHash = substr($data, self::SALT_LENGTH);
        $computedHash = hash_hmac('sha256', $password, $salt, true);

        // So sánh constant-time (tương đương SlowEquals)
        return hash_equals($storedHash, $computedHash);
    }
}
