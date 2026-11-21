@extends('layouts.admin')

@section('title', 'Báo cáo đơn hàng')

@section('styles')
    <style>
        .chart-wrapper {
            position: relative;
            min-height: 320px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-4">
        <div class="mb-2">
            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-link btn-sm text-decoration-none text-muted p-0">
                <i class="fas fa-long-arrow-alt-left me-1"></i> Quay lại
            </a>
        </div>
        <h4 class="fw-bold mb-3">📦 BÁO CÁO ĐƠN HÀNG</h4>

        @include('admin.partials.date-filter')

        <!-- CHART -->
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Số đơn theo ngày</div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card shadow">
            <div class="card-header fw-bold">Danh sách đơn hàng ({{ $orders->count() }})</div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Mã đơn</th>
                        <th>Ngày</th>
                        <th>Khách</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Tổng</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($orders as $o)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><a href="{{ route('admin.order.details', $o->OrderID) }}">#{{ $o->OrderID }}</a></td>
                            <td>{{ $o->OrderDate?->format('d/m/Y H:i') }}</td>
                            <td>{{ $o->customer?->UserName ?? 'N/A' }}</td>
                            <td>{{ \App\Enums\OrderStatus::labelOf($o->Status) }}</td>
                            <td class="text-end">{{ n0($o->TotalAmount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Không có đơn hàng trong khoảng thời gian này</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        $.getJSON(@json(route('admin.dashboard.orders-by-day')), {
            fromDate: @json($fromDate),
            toDate: @json($toDate)
        }, function (res) {
            const statusMap = [
                { key: 'Pending', label: 'Chờ xác nhận', color: '#ffc107' },
                { key: 'Processing', label: 'Đang xử lý', color: '#17a2b8' },
                { key: 'Shipped', label: 'Đang giao', color: '#007bff' },
                { key: 'Completed', label: 'Hoàn thành', color: '#28a745' },
                { key: 'Cancelled', label: 'Đã hủy', color: '#dc3545' }
            ];

            new Chart(document.getElementById('ordersChart'), {
                type: 'bar',
                data: {
                    labels: res.map(x => x.Date),
                    datasets: statusMap.map(s => ({
                        label: s.label,
                        data: res.map(x => x[s.key]),
                        backgroundColor: s.color,
                        borderColor: s.color,
                        borderWidth: 1
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        });
    </script>
@endsection
