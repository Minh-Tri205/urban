@extends('layouts.admin')

@section('title', 'Báo cáo doanh thu')

@section('content')
    <div class="container-fluid mt-4">
        <div class="mb-2">
            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-link btn-sm text-decoration-none text-muted p-0">
                <i class="fas fa-long-arrow-alt-left me-1"></i> Quay lại
            </a>
        </div>
        <h4 class="fw-bold mb-3">💰 BÁO CÁO DOANH THU</h4>

        @include('admin.partials.date-filter')

        <div class="card mb-4">
            <div class="card-header fw-bold">Doanh thu theo ngày</div>
            <div class="card-body" style="height:350px">
                <canvas id="revChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-bold d-flex justify-content-between">
                <span>Chi tiết đơn hoàn thành ({{ $orders->count() }})</span>
                <span class="text-danger">Tổng: {{ n0($orders->sum(fn ($o) => (float) $o->TotalAmount)) }} đ</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Mã đơn</th>
                        <th>Ngày</th>
                        <th>Khách</th>
                        <th class="text-end">Doanh thu</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($orders as $o)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><a href="{{ route('admin.order.details', $o->OrderID) }}">#{{ $o->OrderID }}</a></td>
                            <td>{{ $o->OrderDate?->format('d/m/Y H:i') }}</td>
                            <td>{{ $o->customer?->UserName ?? 'N/A' }}</td>
                            <td class="text-end">{{ n0($o->TotalAmount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Không có đơn hoàn thành trong khoảng thời gian này</td></tr>
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
        $.getJSON(@json(route('admin.dashboard.revenue-by-day')), {
            fromDate: @json($fromDate),
            toDate: @json($toDate)
        }, function (data) {
            new Chart(document.getElementById('revChart'), {
                type: 'line',
                data: {
                    labels: data.map(x => x.Date),
                    datasets: [{
                        label: 'VNĐ',
                        data: data.map(x => x.Total),
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
    </script>
@endsection
