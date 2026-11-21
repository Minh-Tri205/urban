<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'Pending';
    case Processing = 'Processing';
    case Shipped = 'Shipped';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';

    /**
     * Tương đương Enum.TryParse(..., ignoreCase: true) bên C#.
     */
    public static function parse(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }

        return null;
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xác nhận',
            self::Processing => 'Đang xử lý',
            self::Shipped => 'Đang giao',
            self::Completed => 'Hoàn thành',
            self::Cancelled => 'Đã hủy',
        };
    }

    /** Class badge dùng trong trang Admin (Admin/OrderController.Details). */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-warning',
            self::Processing => 'badge-primary',
            self::Shipped => 'badge-info',
            self::Completed => 'badge-success',
            self::Cancelled => 'badge-danger',
        };
    }

    /** Màu Bootstrap 5 dùng ở trang khách hàng: bg-{color} */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Processing => 'primary',
            self::Shipped => 'info',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'bi-clock-fill',
            self::Processing => 'bi-hourglass-split',
            self::Shipped => 'bi-truck',
            self::Completed => 'bi-check-circle-fill',
            self::Cancelled => 'bi-x-circle-fill',
        };
    }

    /** Khách hàng chỉ được tự hủy khi đơn còn Chờ xác nhận hoặc Đang xử lý. */
    public function customerCanCancel(): bool
    {
        return $this === self::Pending || $this === self::Processing;
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Processing, self::Cancelled],
            self::Processing => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Completed],
            self::Completed, self::Cancelled => [],
        };
    }

    public static function labelOf(?string $value): string
    {
        return self::parse($value)?->label() ?? 'Không xác định';
    }
}
