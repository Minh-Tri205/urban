<?php

namespace App\Services;

class ShippingService
{
    // Địa chỉ kho hàng (có thể config sau)
    public const WAREHOUSE_CITY = 'Thành phố Hồ Chí Minh';

    // Phí giao hàng (có thể config sau)
    public const INTRACITY_FEE = 20000; // Nội tỉnh: 20.000 VNĐ
    public const INTERCITY_FEE = 30000; // Liên tỉnh: 30.000 VNĐ

    /**
     * Tính phí giao hàng dựa trên tỉnh/thành phố người nhận.
     */
    public function calculateShippingFee(?string $recipientCity): float
    {
        if ($recipientCity === null || trim($recipientCity) === '') {
            return self::INTERCITY_FEE; // Mặc định liên tỉnh nếu không có thông tin
        }

        $recipient = mb_strtolower($this->normalizeCityName($recipientCity));
        $warehouse = mb_strtolower($this->normalizeCityName(self::WAREHOUSE_CITY));

        return $recipient === $warehouse ? self::INTRACITY_FEE : self::INTERCITY_FEE;
    }

    /**
     * Chuẩn hóa tên tỉnh/thành phố để so sánh.
     * Loại bỏ các tiền tố như "Tỉnh", "Thành phố", "TP.", ...
     */
    private function normalizeCityName(?string $cityName): string
    {
        if ($cityName === null || trim($cityName) === '') {
            return '';
        }

        $normalized = trim($cityName);

        foreach (['Tỉnh ', 'Thành phố ', 'TP. ', 'TP ', 'Tp. ', 'Tp '] as $prefix) {
            if (mb_stripos($normalized, $prefix) === 0) {
                return trim(mb_substr($normalized, mb_strlen($prefix)));
            }
        }

        return $normalized;
    }

    public function getShippingMethodDescription(): string
    {
        return 'Giao hàng trong 3-5 ngày làm việc';
    }
}
