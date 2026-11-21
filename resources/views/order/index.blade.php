@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('styles')
    <link rel="stylesheet" href="{{ asset('Content/css/order.css') }}"/>
@endsection

@section('content')
    @php
        $tabs = [
            null => ['Tất cả', 'bi-list-ul'],
            'Pending' => ['Chờ xác nhận', 'bi-clock'],
            'Processing' => ['Đang xử lý', 'bi-hourglass-split'],
            'Shipped' => ['Đang giao', 'bi-truck'],
            'Completed' => ['Đã giao', 'bi-check-circle'],
            'Cancelled' => ['Đã hủy', 'bi-x-circle'],
        ];
    @endphp

    <section id="orders" class="py-5">
        <div class="container">

            @if (! $hasAnyOrder)
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="bi bi-bag-x" style="font-size: 5rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">Không có đơn hàng nào</h4>
                    <p class="text-muted m-0">Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm!</p>
                    <a href="{{ route('product.index') }}" class="btn btn-primary btn-lg btn-rounded-none fs-6 mt-3">
                        <i class="bi bi-shop me-2"></i>Khám phá sản phẩm
                    </a>
                </div>
            @else
                <!-- Page Title -->
                <div class="mb-4">
                    <h1 class="display-6 mb-2">Đơn hàng của tôi</h1>
                    <p class="text-muted fs-5">Quản lý và theo dõi tất cả đơn hàng của bạn</p>
                </div>

                <!-- Filter Tabs -->
                <div class="mb-4">
                    <ul class="nav nav-pills">
                        @foreach ($tabs as $value => [$label, $icon])
                            <li class="nav-item">
                                <a class="nav-link {{ $currentStatus?->value == $value ? 'active' : '' }}"
                                   href="{{ route('order.index', $value ? ['status' => $value] : []) }}">
                                    <i class="bi {{ $icon }} me-2"></i>{{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($orders->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="mt-2">Không có đơn hàng nào ở trạng thái "{{ $currentStatus?->label() }}".</p>
                    </div>
                @endif

                <!-- Orders Accordion -->
                <div class="accordion" id="ordersAccordion">
                    @foreach ($orders as $order)
                        @php
                            $status = $order->statusEnum();
                            $accordionId = 'order-' . $order->OrderID;
                            $isFirst = $loop->first;
                        @endphp

                        <div class="accordion-item mb-3 border shadow-sm rounded">
                            <h2 class="accordion-header" id="heading-{{ $order->OrderID }}">
                                <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#{{ $accordionId }}"
                                        aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                        aria-controls="{{ $accordionId }}">
                                    <div class="w-100 d-flex justify-content-between align-items-center pe-3">
                                        <div>
                                            <div class="fs-5 mb-1">
                                                Đơn hàng #{{ $order->OrderID }}
                                            </div>
                                            <div class="small text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $order->OrderDate?->format('d/m/Y') }}
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-box-seam me-1"></i>
                                                {{ $order->orderDetails->count() }} sản phẩm
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $status?->color() ?? 'secondary' }} px-3 py-2 fs-6 mb-2 fw-light">
                                                <i class="bi {{ $status?->icon() ?? 'bi-clock-fill' }} me-1"></i>
                                                {{ $status?->label() ?? $order->Status }}
                                            </span>
                                            <div class="fw-bold fs-4 " style="font-family: Roboto">
                                                {{ vnd($order->TotalAmount) }}
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="{{ $accordionId }}"
                                 class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
                                 aria-labelledby="heading-{{ $order->OrderID }}"
                                 data-bs-parent="#ordersAccordion">
                                <div class="accordion-body">
                                    <!-- Order Info -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <h6 class=" mb-3">
                                                        <i class="bi bi-geo-alt text-primary me-2"></i>
                                                        Địa chỉ giao hàng
                                                    </h6>
                                                    <p class="mb-1 fw-semibold">{{ $order->FullName }} - {{ $order->PhoneNumber }}</p>
                                                    <p class="mb-0">{{ $order->ShippingAddress }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-3 mt-md-0">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <h6 class=" mb-3">
                                                        <i class="bi bi-credit-card text-primary me-2"></i>
                                                        Thanh toán
                                                    </h6>
                                                    <p class="mb-0">{{ $order->PaymentMethod }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Order Items -->
                                    <h6 class=" mb-3">
                                        <i class="bi bi-bag-check text-primary me-2"></i>
                                        Chi tiết sản phẩm
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                            <tr>
                                                <th width="50%">Sản phẩm</th>
                                                <th class="text-center">Đơn giá</th>
                                                <th class="text-center">Số lượng</th>
                                                <th class="text-end">Thành tiền</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($order->orderDetails as $detail)
                                                @php
                                                    $variant = $detail->productVariant;
                                                    $product = $variant?->product;
                                                    $discount = (float) ($detail->DiscountPercent ?? 0);
                                                    $unitPrice = (float) $detail->Price * (1 - $discount / 100);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3" style="width: 80px; height: 80px;">
                                                                <img src="{{ $product ? $product->primaryImageUrl() : asset('Content/img/products/default.jpg') }}"
                                                                     alt="{{ $product?->ProductName }}"
                                                                     class="img-fluid rounded"
                                                                     style="width: 100%; height: 100%; object-fit: cover;">
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1 ">{{ $product?->ProductName ?? 'Sản phẩm không xác định' }}</h6>
                                                                <div class="small text-muted">
                                                                    <span>Màu: <strong>{{ $variant?->color?->ColorName ?? 'N/A' }}</strong></span>
                                                                    <span class="mx-2">|</span>
                                                                    <span>Size: <strong>{{ $variant?->size?->SizeName ?? 'N/A' }}</strong></span>
                                                                </div>
                                                                @if ($discount > 0)
                                                                    <span class="badge bg-danger mt-1">-{{ n0($discount) }}%</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($discount > 0)
                                                            <div class="text-muted text-decoration-line-through small">
                                                                {{ vnd($detail->Price) }}
                                                            </div>
                                                        @endif
                                                        <div class="">{{ vnd($unitPrice) }}</div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="">{{ $detail->Quantity }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="fs-5">
                                                            {{ vnd($unitPrice * $detail->Quantity) }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr class="table-light">
                                                <td colspan="3" class="text-end">Phí vận chuyển:</td>
                                                <td class="text-end">{{ vnd($order->ShippingFee) }}</td>
                                            </tr>
                                            <tr class="table-light">
                                                <td colspan="3" class="text-end  fs-5">Tổng cộng:</td>
                                                <td class="text-end  fw-bold fs-4">
                                                    {{ vnd($order->TotalAmount) }}
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2 mt-4">
                                        @if ($status?->customerCanCancel())
                                            <form action="{{ route('order.cancel', $order->OrderID) }}" method="post" class="d-inline"
                                                  onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="bi bi-x-circle me-2"></i>Hủy đơn hàng
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('thongke.details', $order->OrderID) }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-receipt me-2"></i>Xem hóa đơn
                                        </a>
                                        <a href="#" class="btn btn-outline-primary ms-auto" onclick="window.print(); return false;">
                                            <i class="bi bi-printer me-2"></i>In hóa đơn
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
