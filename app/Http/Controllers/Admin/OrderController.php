<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOperationException;
use App\Helpers\CustomerSession;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderController extends Controller
{
    private const PAGE_SIZE = 10;

    public function __construct(private OrderService $orderService)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $status = OrderStatus::parse($request->query('status'));
        $fromDate = $request->query('fromDate');
        $validFromDate = is_string($fromDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) && strtotime($fromDate) !== false;

        $orders = Order::with('orderDetails.productVariant.product')
            ->when($search !== '', fn ($q) => $q->where('OrderID', ctype_digit(ltrim($search, '#')) ? (int) ltrim($search, '#') : 0))
            ->when($status, fn ($q) => $q->where('Status', $status->value))
            ->when($validFromDate, fn ($q) => $q->where('OrderDate', '>=', $fromDate . ' 00:00:00'))
            ->orderByDesc('OrderDate')
            ->orderByDesc('OrderID')
            ->paginate(self::PAGE_SIZE)
            ->withQueryString();

        return view('admin.order.index', [
            'orders' => $orders,
            'statusCounts' => Order::groupBy('Status')->get(['Status', DB::raw('COUNT(*) as Total')])->pluck('Total', 'Status'),
            'search' => $search,
            'status' => $status,
            'fromDate' => $validFromDate ? $fromDate : '',
        ]);
    }

    public function details(string $id)
    {
        $order = ctype_digit($id)
            ? Order::with([
                'customer',
                'orderDetails.productVariant.product.productImages',
                'orderDetails.productVariant.color',
                'orderDetails.productVariant.size',
                'statusHistories' => fn ($q) => $q->orderBy('ChangedAt')->orderBy('HistoryID'),
            ])->find((int) $id)
            : null;

        abort_if($order === null, 404);

        $subTotal = $order->orderDetails->sum(fn ($d) => (float) $d->Price * $d->Quantity);
        $totalDiscount = $order->orderDetails->sum(fn ($d) => (float) $d->Price * $d->Quantity * (float) ($d->DiscountPercent ?? 0) / 100);
        $shippingFee = (float) ($order->ShippingFee ?? 0);

        return view('admin.order.details', [
            'order' => $order,
            'status' => $order->statusEnum(),
            'subTotal' => $subTotal,
            'totalDiscount' => $totalDiscount,
            'shippingFee' => $shippingFee,
            'grandTotal' => $subTotal - $totalDiscount + $shippingFee,
        ]);
    }

    public function editStatus(Request $request, ?string $id = null)
    {
        $orderId = (int) ($id ?? $request->input('id'));
        $newStatus = OrderStatus::parse($request->input('status'));
        $note = mb_substr(trim((string) $request->input('note')), 0, 500) ?: null;

        $order = Order::find($orderId);

        if ($order === null) {
            return redirect()->route('admin.order.index')->with('Error', 'Đơn hàng không tồn tại.');
        }

        $back = redirect()->route('admin.order.details', $orderId);

        if ($newStatus === null) {
            return $back->with('Error', 'Trạng thái không hợp lệ.');
        }

        try {
            $this->changeStatus($order, $newStatus, $note);
        } catch (InvalidOperationException $e) {
            return $back->with('Error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return $back->with('Error', 'Có lỗi xảy ra khi cập nhật trạng thái. Vui lòng thử lại.');
        }

        return $back->with('Success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    public function editManyStatus(Request $request)
    {
        $ids = collect(explode(',', (string) $request->input('id')))
            ->map(fn ($v) => trim($v))
            ->filter(fn ($v) => ctype_digit($v))
            ->map(fn ($v) => (int) $v)
            ->unique();
        $newStatus = OrderStatus::parse($request->input('status'));

        if ($ids->isEmpty() || $newStatus === null) {
            return redirect()->route('admin.order.index')->with('Error', 'Dữ liệu không hợp lệ.');
        }

        $success = 0;
        $failed = 0;

        foreach (Order::whereIn('OrderID', $ids)->get() as $order) {
            try {
                $this->changeStatus($order, $newStatus, null);
                $success++;
            } catch (Throwable $e) {
                if (! $e instanceof InvalidOperationException) {
                    report($e);
                }
                $failed++;
            }
        }

        $failed += $ids->count() - $success - $failed;

        $redirect = redirect()->route('admin.order.index');

        if ($success > 0) {
            $redirect->with('Success', "Đã cập nhật {$success} đơn hàng.");
        }
        if ($failed > 0) {
            $redirect->with('Error', "Không thể cập nhật {$failed} đơn hàng (trạng thái hiện tại không cho phép chuyển sang \"{$newStatus->label()}\").");
        }

        return $redirect;
    }

    /**
     * Đổi trạng thái trong transaction, khóa dòng đơn hàng để hai người cùng thao tác không hoàn kho 2 lần.
     *
     * @throws InvalidOperationException
     */
    private function changeStatus(Order $order, OrderStatus $newStatus, ?string $note): void
    {
        DB::transaction(function () use ($order, $newStatus, $note) {
            $locked = Order::with('orderDetails')->lockForUpdate()->findOrFail($order->OrderID);
            $oldStatus = $locked->Status;

            if (! $this->orderService->canChangeStatus($oldStatus, $newStatus)) {
                throw new InvalidOperationException(sprintf(
                    "Không thể chuyển đổi trạng thái từ '%s' sang '%s'.",
                    OrderStatus::labelOf($oldStatus),
                    $newStatus->label()
                ));
            }

            $locked->update(['Status' => $newStatus->value]);

            match ($newStatus) {
                OrderStatus::Cancelled => $this->orderService->onOrderCancelled($locked),
                OrderStatus::Completed => $this->orderService->onOrderCompleted($locked),
                default => null,
            };

            $this->orderService->logStatusChange(
                $locked->OrderID,
                $oldStatus,
                $newStatus->value,
                CustomerSession::user()?->UserName ?? 'Admin',
                $note
            );
        });
    }
}
