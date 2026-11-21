<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOperationException;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use Throwable;

/**
 * Port từ Services/OrderService.cs.
 * Các hàm thay đổi tồn kho nên được gọi bên trong DB::transaction() ở controller.
 */
class OrderService
{
    public function canChangeStatus(OrderStatus|string|null $fromStatus, OrderStatus|string|null $toStatus): bool
    {
        $from = $fromStatus instanceof OrderStatus ? $fromStatus : OrderStatus::parse($fromStatus);
        $to = $toStatus instanceof OrderStatus ? $toStatus : OrderStatus::parse($toStatus);

        if ($from === null || $to === null || $from === $to) {
            return false;
        }

        return in_array($to, $from->allowedTransitions(), true);
    }

    /**
     * @param iterable<object|array{VariantID:int, Quantity:int}> $cartItems
     *
     * @throws InvalidOperationException
     */
    public function validateStockForOrder(iterable $cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            $this->findVariantWithStock($cartItem);
        }
    }

    /**
     * Trừ tồn kho theo từng dòng giỏ hàng (khóa dòng để tránh bán vượt tồn kho).
     *
     * @param iterable<object|array{VariantID:int, Quantity:int}> $cartItems
     *
     * @throws InvalidOperationException
     */
    public function deductStock(iterable $cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            $variant = $this->findVariantWithStock($cartItem, lock: true);
            $variant->Stock -= (int) data_get($cartItem, 'Quantity');

            if ($variant->Stock < 0) {
                throw new InvalidOperationException("Tồn kho không được âm (mã biến thể {$variant->VariantID}).");
            }

            $variant->save();
        }
    }

    public function restoreStock(Order $order): void
    {
        foreach ($order->orderDetails as $orderDetail) {
            ProductVariant::whereKey($orderDetail->VariantID)->increment('Stock', $orderDetail->Quantity);
        }
    }

    public function logStatusChange(int $orderId, ?string $oldStatus, string $newStatus, ?string $changedBy, ?string $note = null): void
    {
        try {
            OrderStatusHistory::create([
                'OrderID' => $orderId,
                'OldStatus' => $oldStatus,
                'NewStatus' => $newStatus,
                'ChangedBy' => $changedBy ?? 'System',
                'ChangedAt' => now(),
                'Note' => $note,
            ]);
        } catch (Throwable $e) {
            // Bản gốc bỏ qua lỗi ghi lịch sử để không chặn việc đổi trạng thái; ở đây ghi log lại
            report($e);
        }
    }

    public function onOrderCompleted(Order $order): void
    {
        if ($order->Status !== OrderStatus::Completed->value) {
            return;
        }

        // Điểm mở rộng: tích điểm, gửi email, cập nhật thống kê doanh thu
    }

    public function onOrderCancelled(Order $order): void
    {
        if ($order->Status !== OrderStatus::Cancelled->value) {
            return;
        }

        // Hoàn lại tồn kho. Đơn COD vẫn giữ trạng thái chưa thanh toán.
        $this->restoreStock($order);
    }

    private function findVariantWithStock(object|array $cartItem, bool $lock = false): ProductVariant
    {
        $variantId = (int) data_get($cartItem, 'VariantID');
        $quantity = (int) data_get($cartItem, 'Quantity');

        $query = ProductVariant::with('product')->whereKey($variantId);
        $variant = ($lock ? $query->lockForUpdate() : $query)->first();

        if ($variant === null) {
            throw new InvalidOperationException("Sản phẩm (mã biến thể {$variantId}) không tồn tại hoặc đã bị xóa.");
        }

        if ($quantity > $variant->Stock) {
            throw new InvalidOperationException(
                "Sản phẩm {$variant->product?->ProductName} không đủ hàng. "
                . "Số lượng đặt: {$quantity}, còn lại: {$variant->Stock}."
            );
        }

        return $variant;
    }
}
