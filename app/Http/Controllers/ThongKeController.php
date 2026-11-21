<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Helpers\CustomerSession;
use App\Models\Order;

class ThongKeController extends Controller
{
    public function index()
    {
        $orders = Order::where('CustomerID', CustomerSession::id())->orderByDesc('OrderID')->get();
        $completed = $orders->where('Status', OrderStatus::Completed->value);

        return view('thongke.index', [
            'orders' => $orders,
            'totalOrders' => $orders->count(),
            'totalSpent' => $completed->sum(fn (Order $order) => (float) $order->TotalAmount),
            'completed' => $completed->count(),
            'shipping' => $orders->whereNotIn('Status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])->count(),
        ]);
    }

    public function details(string $id)
    {
        $order = ctype_digit($id)
            ? Order::with([
                'orderDetails.productVariant.product',
                'orderDetails.productVariant.size',
                'orderDetails.productVariant.color',
            ])->where('CustomerID', CustomerSession::id())->find($id)
            : null;

        abort_if($order === null, 404);

        return view('thongke.details', compact('order'));
    }
}
