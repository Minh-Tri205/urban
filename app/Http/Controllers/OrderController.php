<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOperationException;
use App\Helpers\CustomerSession;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = OrderStatus::parse($request->query('status'));
        $customerId = CustomerSession::id();

        $orders = Order::with([
            'orderDetails.productVariant.product.productImages',
            'orderDetails.productVariant.color',
            'orderDetails.productVariant.size',
        ])
            ->where('CustomerID', $customerId)
            ->when($status, fn ($query) => $query->where('Status', $status->value))
            ->orderByDesc('OrderID')
            ->get();

        return view('order.index', [
            'orders' => $orders,
            'currentStatus' => $status,
            'hasAnyOrder' => $orders->isNotEmpty() || Order::where('CustomerID', $customerId)->exists(),
        ]);
    }

    public function cancel(Request $request, OrderService $orderService, ?string $id = null)
    {
        $back = $this->backUrl($request);
        $order = ctype_digit((string) $id) ? Order::with('orderDetails')->find($id) : null;

        if ($order === null) {
            return redirect($back)->with('Error', 'Đơn hàng không tồn tại.');
        }

        $customer = CustomerSession::user();

        if ((int) $order->CustomerID !== $customer->CustomerID) {
            return redirect($back)->with('Error', 'Bạn không có quyền hủy đơn hàng này.');
        }

        $currentStatus = $order->Status;

        if (! $order->statusEnum()?->customerCanCancel()) {
            return redirect($back)->with('Error', "Chỉ có thể hủy đơn hàng khi đơn hàng đang ở trạng thái 'Chờ xác nhận' hoặc 'Đang xử lý'. Đơn hàng đã được giao không thể hủy.");
        }

        if (! $orderService->canChangeStatus($currentStatus, OrderStatus::Cancelled)) {
            return redirect($back)->with('Error', 'Không thể hủy đơn hàng ở trạng thái hiện tại.');
        }

        try {
            DB::transaction(function () use ($order, $currentStatus, $orderService, $customer) {
                // Khóa đơn rồi kiểm tra lại trạng thái để tránh hoàn kho 2 lần khi bấm hủy liên tục
                $locked = Order::whereKey($order->OrderID)->lockForUpdate()->first();
                if ($locked->Status !== $currentStatus) {
                    throw new InvalidOperationException('Trạng thái đơn hàng đã thay đổi. Vui lòng tải lại trang.');
                }

                $order->update(['Status' => OrderStatus::Cancelled->value]);
                $orderService->onOrderCancelled($order);
                $orderService->logStatusChange(
                    $order->OrderID,
                    $currentStatus,
                    OrderStatus::Cancelled->value,
                    $customer->UserName ?? 'Customer',
                    'Khách hàng tự hủy đơn hàng'
                );
            });
        } catch (InvalidOperationException $e) {
            return redirect($back)->with('Error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return redirect($back)->with('Error', 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại.');
        }

        return redirect($back)->with('Success', 'Hủy đặt hàng thành công, cảm ơn quý khách.');
    }

    /** Hủy từ trang ThongKe thì quay lại đúng trang đó, còn lại về danh sách đơn hàng. */
    private function backUrl(Request $request): string
    {
        $previous = url()->previous();
        $appUrl = rtrim(url('/'), '/');

        return str_starts_with($previous, $appUrl . '/ThongKe/') ? $previous : route('order.index');
    }
}
