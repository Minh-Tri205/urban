<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Helpers\PasswordHasher;
use App\Rules\ValidFullName;
use App\Rules\VietnamesePhone;
use App\Services\CheckoutValidationService;
use App\Services\OrderService;
use App\Services\ShippingService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FoundationRulesTest extends TestCase
{
    public static function fullNames(): array
    {
        return [
            ['Nguyễn Văn A', true],
            ['Lê Thị Hồng-Nhung', true],
            ['Tr. Minh', true],
            ['A', false],
            ['Nguyen Van 1', false],
            ['   ', false],
            [str_repeat('a', 101), false],
        ];
    }

    #[DataProvider('fullNames')]
    public function test_valid_full_name(string $name, bool $expected): void
    {
        $this->assertSame($expected, ValidFullName::passes($name));
    }

    public static function phones(): array
    {
        return [
            ['0901234567', true],
            ['090 123-4567', true],
            ['+84901234567', true],
            ['09012345678', true],
            ['0012345678', false],
            ['090123456', false],
            ['abc', false],
        ];
    }

    #[DataProvider('phones')]
    public function test_vietnamese_phone(string $phone, bool $expected): void
    {
        $this->assertSame($expected, VietnamesePhone::passes($phone));
    }

    public function test_order_status_transitions(): void
    {
        $service = new OrderService();

        $this->assertTrue($service->canChangeStatus('Pending', 'Processing'));
        $this->assertTrue($service->canChangeStatus('pending', 'cancelled'));
        $this->assertTrue($service->canChangeStatus(OrderStatus::Shipped, OrderStatus::Completed));
        $this->assertFalse($service->canChangeStatus('Pending', 'Pending'));
        $this->assertFalse($service->canChangeStatus('Shipped', 'Cancelled'));
        $this->assertFalse($service->canChangeStatus('Completed', 'Pending'));
        $this->assertFalse($service->canChangeStatus('Unknown', 'Pending'));
        $this->assertSame('Chờ xác nhận', OrderStatus::labelOf('Pending'));
        $this->assertSame('Không xác định', OrderStatus::labelOf('abc'));
    }

    public function test_shipping_fee(): void
    {
        $service = new ShippingService();

        $this->assertEquals(20000, $service->calculateShippingFee('Hồ Chí Minh'));
        $this->assertEquals(20000, $service->calculateShippingFee('TP. Hồ Chí Minh'));
        $this->assertEquals(20000, $service->calculateShippingFee('thành phố hồ chí minh'));
        $this->assertEquals(30000, $service->calculateShippingFee('Hà Nội'));
        $this->assertEquals(30000, $service->calculateShippingFee(null));
    }

    public function test_checkout_validation(): void
    {
        $service = new CheckoutValidationService();
        $valid = [
            'CustomerProfile' => ['FullName' => 'Nguyễn Văn A', 'PhoneNumber' => '0901234567', 'City' => 'Hồ Chí Minh'],
            'ShippingMethod' => 'Standard',
            'PaymentMethod' => 'COD',
            'ShippingFee' => 20000,
            'SubTotal' => 500000,
            'TotalDiscount' => 50000,
            'GrandTotal' => 470000,
        ];
        $items = [['VariantID' => 1, 'Quantity' => 1]];

        $this->assertTrue($service->validateCheckout($valid, $items)->isValid);

        $invalid = array_replace($valid, ['PaymentMethod' => 'EWallet', 'GrandTotal' => 1]);
        $invalid['CustomerProfile'] = ['FullName' => 'A1', 'PhoneNumber' => '123'];
        $result = $service->validateCheckout($invalid, []);

        $this->assertFalse($result->isValid);
        $this->assertContains('Số điện thoại không hợp lệ.', $result->errors);
        $this->assertContains('Phương thức thanh toán không hợp lệ.', $result->errors);
        $this->assertContains('Giỏ hàng không có sản phẩm nào.', $result->errors);
        $this->assertCount(5, $result->errors);
    }

    public function test_password_hasher_round_trip(): void
    {
        $hash = PasswordHasher::hashPassword('Mật khẩu@123');

        $this->assertSame(48, strlen(base64_decode($hash)));
        $this->assertTrue(PasswordHasher::verifyPassword('Mật khẩu@123', $hash));
        $this->assertFalse(PasswordHasher::verifyPassword('sai', $hash));
        $this->assertFalse(PasswordHasher::verifyPassword('x', 'khong-phai-base64'));
    }
}
