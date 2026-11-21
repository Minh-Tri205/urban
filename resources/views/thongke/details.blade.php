@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->OrderID)

@section('content')
    @php
        $status = $order->statusEnum();
        $statusClass = match ($status) {
            \App\Enums\OrderStatus::Pending => 'warning text-dark',
            \App\Enums\OrderStatus::Shipped => 'info text-white',
            null => 'dark',
            default => $status->color(),
        };
    @endphp

    <div class="container mt-4 mb-5">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary fw-bold">
                    <i class="bi bi-receipt"></i> ĐƠN HÀNG #{{ $order->OrderID }}
                </h5>
                <span class="badge rounded-pill bg-{{ $statusClass }} px-3 py-2">
                    <i class="bi {{ $status?->icon() ?? 'bi-clock-fill' }} me-1"></i>
                    {{ $status?->label() ?? $order->Status }}
                </span>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6 border-end">
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Thông tin giao hàng</p>
                        <h6 class="fw-bold">{{ $order->FullName }}</h6>
                        <p class="mb-1 text-secondary"><i class="bi bi-telephone me-2"></i>{{ $order->PhoneNumber }}</p>
                        <p class="mb-0 text-secondary"><i class="bi bi-geo-alt me-2"></i>{{ $order->ShippingAddress }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Chi tiết đặt hàng</p>
                        <h6>{{ $order->OrderDate?->format('d/m/Y H:i') }}</h6>
                        <p class="text-muted mb-1 text-uppercase small fw-bold mt-2">Thanh toán</p>
                        <span class="badge bg-light text-dark border fw-bold">{{ $order->PaymentMethod }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 45%">Sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($order->orderDetails as $detail)
                            @php
                                $variant = $detail->productVariant;
                                $discount = (float) ($detail->DiscountPercent ?? 0);
                                $unitPrice = (float) $detail->Price * (1 - $discount / 100);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">
                                        {{ $variant?->product?->ProductName ?? 'Sản phẩm không xác định' }}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        @if ($variant)
                                            <span class="badge bg-light text-secondary border-0 ps-0">
                                                Size: {{ $variant->size?->SizeName ?? 'N/A' }},
                                                Màu: {{ $variant->color?->ColorName ?? 'N/A' }}
                                            </span>
                                        @endif
                                        @if ($discount > 0)
                                            <span class="badge bg-danger">-{{ n0($discount) }}%</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">{{ $detail->Quantity }}</td>
                                <td class="text-end">{{ n0($unitPrice) }} đ</td>
                                <td class="text-end fw-bold">{{ n0($unitPrice * $detail->Quantity) }} đ</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3" class="text-end pt-3">Phí vận chuyển:</td>
                            <td class="text-end pt-3">{{ n0($order->ShippingFee) }} đ</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold text-uppercase pt-4">Tổng tiền thanh toán:</td>
                            <td class="text-end text-danger fs-4 fw-bold pt-4">
                                {{ n0($order->TotalAmount) }} đ
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4 border-top pt-3 d-flex justify-content-between">
                    <a href="{{ route('thongke.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left"></i> Quay lại lịch sử
                    </a>
                    @if ($status?->customerCanCancel())
                        <form action="{{ route('order.cancel', $order->OrderID) }}" method="post"
                              onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng #{{ $order->OrderID }} không?');">
                            @csrf
                            <button type="submit" class="btn btn-danger px-4">Hủy đơn hàng</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
