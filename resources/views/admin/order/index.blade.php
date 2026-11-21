@extends('layouts.admin')

@section('title', 'Quản lý Đơn hàng')

@section('content')
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <span>Đơn hàng</span>
    </div>

    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h1>Quản lý Đơn hàng</h1>
                <p class="text-muted mt-1">Danh sách tất cả đơn hàng trong hệ thống</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">Tìm kiếm & Lọc</h3>
            </div>
            <div class="card-content">
                <form method="get" action="{{ route('admin.order.index') }}" id="filterForm">
                    <div class="grid grid-cols-3">
                        <input type="search" name="search" id="searchInput" class="form-control"
                               placeholder="Tìm theo mã đơn... vd: 123" value="{{ $search }}">

                        <select name="status" id="statusFilter" class="form-control">
                            <option value="">Tất cả trạng thái</option>
                            @foreach (\App\Enums\OrderStatus::cases() as $option)
                                <option value="{{ $option->value }}" @selected($status === $option)>{{ $option->label() }}</option>
                            @endforeach
                        </select>

                        <input type="date" name="fromDate" class="form-control" value="{{ $fromDate }}" aria-label="Từ ngày">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mt-3">Lọc</button>
                    <a href="{{ route('admin.order.index') }}" class="btn btn-outline btn-sm mt-3">Xóa bộ lọc</a>
                </form>
            </div>
        </div>

        <!-- Statistics (toàn bộ đơn hàng) -->
        <div class="grid mb-6" style="grid-template-columns: repeat(5, 1fr);">
            @foreach (\App\Enums\OrderStatus::cases() as $option)
                <a class="card text-decoration-none text-reset" href="{{ route('admin.order.index', ['status' => $option->value]) }}">
                    <div class="card-content" style="text-align: center;">
                        <h2>{{ (int) ($statusCounts[$option->value] ?? 0) }}</h2>
                        <p class="text-muted" style="font-size: 12px; margin-top: 0.5rem;">{{ $option->label() }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Bulk Actions Bar -->
        <div id="bulkActionsBar" class="card mb-6 hidden" style="background-color: var(--accent);">
            <div class="card-content">
                <div class="d-flex align-items-center gap-3" style="flex-wrap: wrap;">
                    <span><strong><span id="selectedCount">0</span></strong> đơn hàng đã chọn</span>
                    <span class="text-muted">Đổi trạng thái sang:</span>
                    <button type="button" class="btn btn-primary btn-sm" onclick="bulkUpdateStatus('Processing')">Đang xử lý</button>
                    <button type="button" class="btn btn-info btn-sm" onclick="bulkUpdateStatus('Shipped')">Đang giao</button>
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkUpdateStatus('Completed')">Hoàn thành</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkUpdateStatus('Cancelled')">Hủy đơn</button>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-content" style="padding: 0;">
                <div class="table-container">
                    <table class="table" id="dataTable">
                        <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAll" class="checkbox" aria-label="Chọn tất cả">
                            </th>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th style="width: 150px;">Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($orders as $order)
                            @php $orderStatus = $order->statusEnum(); @endphp
                            <tr>
                                <td><input data-id="{{ $order->OrderID }}" type="checkbox" class="checkbox row-checkbox" aria-label="Chọn đơn hàng"></td>
                                <td><strong>#{{ $order->OrderID }}</strong></td>
                                <td>
                                    <div>
                                        <strong>{{ $order->FullName }}</strong>
                                        <p class="text-muted mb-0" style="font-size: 12px;">{{ $order->PhoneNumber }}</p>
                                    </div>
                                </td>
                                <td>
                                    <div class="w-100 text-truncate flex-nowrap" style="max-width: 200px;">
                                        {{ $order->orderDetails->map(fn ($d) => $d->productVariant?->product?->ProductName)->filter()->take(2)->implode(', ') }}
                                        <p class="text-muted mb-0" style="font-size: 12px;">{{ $order->orderDetails->count() }} sản phẩm</p>
                                    </div>
                                </td>
                                <td><strong>{{ n0($order->TotalAmount) }} ₫</strong></td>
                                <td>
                                    <span class="badge {{ $orderStatus?->badgeClass() ?? 'badge-secondary' }}">{{ $orderStatus?->label() ?? 'Không xác định' }}</span>
                                </td>
                                <td>{{ $order->OrderDate?->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('admin.order.details', $order->OrderID) }}" class="btn btn-outline btn-sm">Xem</a>
                                        @if ($orderStatus && $orderStatus->allowedTransitions() !== [])
                                            <form action="{{ route('admin.order.edit-status', $order->OrderID) }}" method="post" class="d-flex gap-1">
                                                @csrf
                                                <select name="status" class="form-control" style="padding: 0.25rem; font-size: 12px;" aria-label="Trạng thái mới">
                                                    @foreach ($orderStatus->allowedTransitions() as $option)
                                                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-primary btn-sm">Đổi</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div>Không tìm thấy đơn hàng nào.</div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @include('admin.partials.pagination', ['paginator' => $orders])
    </div>
@endsection

@section('forms')
    <form action="{{ route('admin.order.edit-many-status') }}" method="post" class="d-none" id="form-bulk-status">
        @csrf
        <input type="hidden" name="id">
        <input type="hidden" name="status">
    </form>
@endsection

@section('scripts')
    <script>
        function bulkUpdateStatus(status) {
            const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.dataset.id);
            if (ids.length === 0) {
                showToast('Vui lòng chọn ít nhất một đơn hàng', 'error');
                return;
            }
            const form = document.getElementById('form-bulk-status');
            form.elements['id'].value = ids.join(',');
            form.elements['status'].value = status;
            form.submit();
        }
    </script>
@endsection
