@extends('layouts.admin')

@section('title', 'Chi tiết Đơn hàng #' . $order->OrderID)

@use('App\Enums\OrderStatus')

@section('content')
    @php
        $dotColor = fn (?string $value) => match (OrderStatus::parse($value)) {
            OrderStatus::Completed => 'var(--success)',
            OrderStatus::Cancelled => 'var(--destructive)',
            OrderStatus::Pending => 'var(--warning)',
            null => 'var(--border)',
            default => 'var(--primary)',
        };
        $histories = $order->statusHistories;
        $canChange = $status && $status->allowedTransitions() !== [];
    @endphp

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('admin.order.index') }}">Đơn hàng</a>
        <span class="breadcrumb-separator">/</span>
        <span>Chi tiết đơn hàng</span>
    </div>

    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div class="d-flex align-items-center gap-4">
                <a href="{{ route('admin.order.index') }}" class="btn btn-icon btn-outline" aria-label="Quay lại">
                    <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <div>
                    <h1>Chi tiết Đơn hàng #{{ $order->OrderID }}</h1>
                    <p class="text-muted mt-1">Ngày đặt: {{ $order->OrderDate?->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline" onclick="window.print()">
                    <svg style="width: 16px; height: 16px; margin-right: 0.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    In đơn hàng
                </button>
                @if ($canChange)
                    <a href="#capNhatTrangThai" class="btn btn-primary">Cập nhật trạng thái</a>
                @endif
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
            <!-- Left Column -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Status Timeline (lấy từ bảng OrderStatusHistory) -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Trạng thái đơn hàng</h3>
                            <span class="badge {{ $status?->badgeClass() ?? 'badge-secondary' }}">{{ $status?->label() ?? 'Không xác định' }}</span>
                        </div>
                    </div>
                    <div class="card-content">
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            @if ($histories->isEmpty() || $histories->first()->OldStatus !== null)
                                {{-- Đơn cũ không có bản ghi tạo đơn: hiển thị mốc đặt hàng từ OrderDate --}}
                                <div class="d-flex gap-3">
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div style="width: 12px; height: 12px; border-radius: 50%; background: var(--success);"></div>
                                        @if ($histories->isNotEmpty())
                                            <div style="width: 2px; flex: 1; min-height: 32px; background: var(--border);"></div>
                                        @endif
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="d-flex justify-content-between">
                                            <strong>Đơn hàng đã đặt</strong>
                                            <span class="text-muted">{{ $order->OrderDate?->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <p class="text-muted" style="font-size: 12px; margin-top: 0.25rem;">Đơn hàng được tạo thành công</p>
                                    </div>
                                </div>
                            @endif

                            @foreach ($histories as $history)
                                <div class="d-flex gap-3">
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div style="width: 12px; height: 12px; border-radius: 50%; background: {{ $dotColor($history->NewStatus) }};"></div>
                                        @unless ($loop->last)
                                            <div style="width: 2px; flex: 1; min-height: 32px; background: var(--border);"></div>
                                        @endunless
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="d-flex justify-content-between">
                                            <strong @if ($history->NewStatus === 'Cancelled') style="color: var(--destructive);" @endif>
                                                {{ $history->OldStatus === null ? 'Đơn hàng đã đặt' : OrderStatus::labelOf($history->NewStatus) }}
                                            </strong>
                                            <span class="text-muted">{{ $history->ChangedAt?->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <p class="text-muted mb-0" style="font-size: 12px; margin-top: 0.25rem;">
                                            @if ($history->OldStatus !== null)
                                                {{ OrderStatus::labelOf($history->OldStatus) }} → {{ OrderStatus::labelOf($history->NewStatus) }} ·
                                            @endif
                                            bởi <strong>{{ $history->ChangedBy }}</strong>
                                        </p>
                                        @if ($history->Note && $history->Note !== 'Order created')
                                            <p class="mb-0" style="font-size: 13px;">Ghi chú: {{ $history->Note }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($canChange)
                            <hr>
                            <form action="{{ route('admin.order.edit-status', $order->OrderID) }}" method="post" id="capNhatTrangThai">
                                @csrf
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="form-group mb-0">
                                        <label class="form-label" for="newStatus">Chuyển sang trạng thái</label>
                                        <select name="status" id="newStatus" class="form-control" required>
                                            @foreach ($status->allowedTransitions() as $option)
                                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="form-label" for="statusNote">Ghi chú (tùy chọn)</label>
                                        <input type="text" id="statusNote" name="note" class="form-control" maxlength="500"
                                               placeholder="Ví dụ: khách hẹn giao lại ngày mai">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Cập nhật trạng thái</button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Products -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sản phẩm</h3>
                    </div>
                    <div class="card-content" style="padding: 0;">
                        <div class="table-container">
                            <table class="table m-0">
                                <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th style="text-align: right;">Đơn giá</th>
                                    <th style="text-align: center;">Số lượng</th>
                                    <th style="text-align: right;">Tổng</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($order->orderDetails as $item)
                                    @php
                                        $variant = $item->productVariant;
                                        $product = $variant?->product;
                                        $discount = (float) ($item->DiscountPercent ?? 0);
                                        $priceAfterDiscount = (float) $item->Price * (1 - $discount / 100);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $product ? $product->primaryImageUrl() : asset('Content/img/products/default.jpg') }}"
                                                     alt="{{ $product?->ProductName }}"
                                                     style="width: 48px; height: 48px; object-fit: cover; border-radius: var(--radius);">
                                                <div>
                                                    <h6 class="mb-1">{{ $product?->ProductName ?? 'Sản phẩm không xác định' }}</h6>
                                                    <div class="small text-muted">
                                                        <span>Màu: <strong>{{ $variant?->color?->ColorName ?? 'N/A' }}</strong></span>
                                                        <span class="mx-2">|</span>
                                                        <span>Size: <strong>{{ $variant?->size?->SizeName ?? 'N/A' }}</strong></span>
                                                        <span class="mx-2">|</span>
                                                        <span>SKU: {{ $variant?->SKU }}</span>
                                                    </div>
                                                    @if ($discount > 0)
                                                        <span class="badge bg-danger mt-1">-{{ n0($discount) }}%</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td style="text-align: right;">
                                            @if ($discount > 0)
                                                <div class="text-muted text-decoration-line-through small">{{ n0($item->Price) }}₫</div>
                                            @endif
                                            {{ n0($priceAfterDiscount) }}₫
                                        </td>
                                        <td style="text-align: center;">{{ $item->Quantity }}</td>
                                        <td style="text-align: right;"><strong>{{ n0($priceAfterDiscount * $item->Quantity) }}₫</strong></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div style="padding: 1.5rem; border-top: 1px solid var(--border);">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <div class="d-flex justify-content-between text-muted">
                                    <span>Tạm tính</span>
                                    <span>{{ n0($subTotal) }}₫</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>Phí vận chuyển</span>
                                    <span>{{ n0($shippingFee) }}₫</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>Giảm giá</span>
                                    <span>-{{ n0($totalDiscount) }}₫</span>
                                </div>
                                <div style="height: 1px; background: var(--border); margin: 0.5rem 0;"></div>
                                <div class="d-flex justify-content-between">
                                    <strong>Tổng cộng</strong>
                                    <strong style="font-size: 18px; color: var(--primary);">{{ n0($grandTotal) }}₫</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin khách hàng</h3>
                    </div>
                    <div class="card-content">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 500;">
                                    {{ mb_strtoupper(mb_substr($order->FullName ?: ($order->customer?->UserName ?? '?'), 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $order->FullName }}</strong>
                                    @if ($order->customer)
                                        <p class="text-muted mb-0" style="font-size: 12px;">Tài khoản: {{ $order->customer->UserName }}</p>
                                    @endif
                                </div>
                            </div>
                            <div style="height: 1px; background: var(--border);"></div>
                            <div>
                                <p class="text-muted mb-0" style="font-size: 12px;">Email</p>
                                <p class="mb-0">{{ $order->customer?->Email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size: 12px;">Số điện thoại</p>
                                <p class="mb-0">{{ $order->PhoneNumber }}</p>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size: 12px;">Địa chỉ giao hàng</p>
                                <p class="mb-0">{{ $order->ShippingAddress }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin thanh toán</h3>
                    </div>
                    <div class="card-content">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div>
                                <p class="text-muted mb-0" style="font-size: 12px;">Phương thức thanh toán</p>
                                <strong>{{ $order->PaymentMethod }}</strong>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size: 12px;">Phương thức giao hàng</p>
                                <strong>{{ $order->ShippingMethod }}</strong>
                            </div>
                            <div style="background: var(--muted); padding: 0.75rem; border-radius: var(--radius);">
                                <p class="text-muted mb-0" style="font-size: 12px;">Tổng giá trị đơn hàng</p>
                                <p style="font-size: 20px; font-weight: 600; color: var(--primary); margin: 0.25rem 0 0;">{{ n0($order->TotalAmount) }}₫</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thao tác nhanh</h3>
                    </div>
                    <div class="card-content">
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            @if ($order->customer?->Email)
                                <a class="btn btn-outline w-100" style="justify-content: flex-start;"
                                   href="mailto:{{ $order->customer->Email }}?subject={{ rawurlencode('FashionStore - Đơn hàng #' . $order->OrderID) }}">
                                    Gửi email cho khách hàng
                                </a>
                            @endif
                            @if ($order->PhoneNumber)
                                <a class="btn btn-outline w-100" style="justify-content: flex-start;" href="tel:{{ $order->PhoneNumber }}">
                                    Gọi điện cho khách hàng
                                </a>
                            @endif
                            @if ($status && in_array(OrderStatus::Cancelled, $status->allowedTransitions(), true))
                                <form action="{{ route('admin.order.edit-status', $order->OrderID) }}" method="post"
                                      onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không? Tồn kho sẽ được hoàn lại.');">
                                    @csrf
                                    <input type="hidden" name="status" value="Cancelled">
                                    <input type="hidden" name="note" value="Quản trị viên hủy đơn">
                                    <button type="submit" class="btn btn-outline w-100 text-danger" style="justify-content: flex-start;">
                                        Hủy đơn hàng
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
