<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Rules\ValidFullName;
use App\Rules\VietnamesePhone;

/**
 * Port từ Services/CheckoutValidationService.cs.
 *
 * $checkout tương đương CheckoutViewModel:
 * [
 *   'CustomerProfile' => ['FullName', 'PhoneNumber', 'Address', 'District', 'City'],
 *   'PaymentMethod', 'ShippingMethod', 'ShippingFee', 'SubTotal', 'TotalDiscount', 'GrandTotal',
 * ]
 */
class CheckoutValidationService
{
    public function validateCheckout(array $checkout, iterable $cartItems): CheckoutValidationResult
    {
        $result = new CheckoutValidationResult();

        // 1. Thông tin người nhận
        $profile = $checkout['CustomerProfile'] ?? null;
        if (! is_array($profile)) {
            $result->addError('Thông tin người nhận không được để trống.');

            return $result;
        }

        $this->validateFullName($profile['FullName'] ?? null, $result);
        $this->validatePhoneNumber($profile['PhoneNumber'] ?? null, $result);
        $this->validateShippingAddress($profile, $result);

        // 2-4. Giao hàng, phí vận chuyển, thanh toán
        $this->validateShippingMethod($checkout['ShippingMethod'] ?? null, $result);
        $this->validateShippingFee((float) ($checkout['ShippingFee'] ?? 0), $result);
        $this->validatePaymentMethod($checkout['PaymentMethod'] ?? null, $result);

        // 5. Đơn hàng (sản phẩm, tổng tiền)
        $this->validateOrder($cartItems, $checkout, $result);

        return $result;
    }

    private function validateFullName(?string $fullName, CheckoutValidationResult $result): void
    {
        $trimmed = trim((string) $fullName);

        if ($trimmed === '') {
            $result->addError('Họ và tên không được để trống.');

            return;
        }

        if (mb_strlen($trimmed) < 2) {
            $result->addError('Họ và tên phải có ít nhất 2 ký tự.');

            return;
        }

        if (mb_strlen($trimmed) > 100) {
            $result->addError('Họ và tên không được vượt quá 100 ký tự.');

            return;
        }

        if (preg_match(ValidFullName::PATTERN, $trimmed) !== 1) {
            $result->addError("Họ và tên chỉ được chứa chữ cái, khoảng trắng, dấu '-' và '.'");
        }
    }

    private function validatePhoneNumber(?string $phoneNumber, CheckoutValidationResult $result): void
    {
        if (trim((string) $phoneNumber) === '') {
            $result->addError('Số điện thoại không được để trống.');

            return;
        }

        if (! VietnamesePhone::passes($phoneNumber)) {
            $result->addError('Số điện thoại không hợp lệ.');
        }
    }

    /** Phải có ít nhất 1 trong: Address, District, City. */
    private function validateShippingAddress(array $profile, CheckoutValidationResult $result): void
    {
        $hasAny = collect(['Address', 'District', 'City'])
            ->contains(fn ($field) => trim((string) ($profile[$field] ?? '')) !== '');

        if (! $hasAny) {
            $result->addError('Vui lòng nhập ít nhất một trong các thông tin: Địa chỉ, Quận/Huyện, hoặc Tỉnh/Thành phố.');
        }
    }

    /** Hiện tại chỉ chấp nhận "Standard". */
    private function validateShippingMethod(?string $shippingMethod, CheckoutValidationResult $result): void
    {
        if (trim((string) $shippingMethod) === '') {
            $result->addError('Phương thức giao hàng không được để trống.');

            return;
        }

        if ($shippingMethod !== ShippingMethod::Standard->value) {
            $result->addError('Phương thức giao hàng không hợp lệ.');
        }
    }

    private function validateShippingFee(float $shippingFee, CheckoutValidationResult $result): void
    {
        if ($shippingFee < 0) {
            $result->addError('Phí vận chuyển không được âm.');
        }
    }

    /** Hiện tại chỉ cho phép "COD". */
    private function validatePaymentMethod(?string $paymentMethod, CheckoutValidationResult $result): void
    {
        if (trim((string) $paymentMethod) === '') {
            $result->addError('Phương thức thanh toán không được để trống.');

            return;
        }

        if ($paymentMethod !== PaymentMethod::COD->value) {
            $result->addError('Phương thức thanh toán không hợp lệ.');
        }
    }

    /** Đơn phải có sản phẩm và GrandTotal = SubTotal - TotalDiscount + ShippingFee. */
    private function validateOrder(iterable $cartItems, array $checkout, CheckoutValidationResult $result): void
    {
        if (collect($cartItems)->isEmpty()) {
            $result->addError('Giỏ hàng không có sản phẩm nào.');

            return;
        }

        $expectedTotal = (float) ($checkout['SubTotal'] ?? 0)
            - (float) ($checkout['TotalDiscount'] ?? 0)
            + (float) ($checkout['ShippingFee'] ?? 0);

        // Cho phép sai số 1 xu do làm tròn
        if (abs((float) ($checkout['GrandTotal'] ?? 0) - $expectedTotal) > 0.01) {
            $result->addError('Tổng tiền đơn hàng không khớp. Vui lòng làm mới trang và thử lại.');
        }
    }
}
