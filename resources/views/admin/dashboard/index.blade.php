@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="container-fluid mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-uppercase m-0">Thống kê & Báo cáo</h3>
                <p class="text-muted small mb-0">Dữ liệu từ {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</p>
            </div>
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-print me-1"></i> Xuất báo cáo
            </button>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="get" action="{{ route('admin.dashboard.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" for="uiFromDate">Từ ngày</label>
                        <input type="date" id="uiFromDate" name="fromDate" value="{{ $fromDate }}" class="form-control"/>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" for="uiToDate">Đến ngày</label>
                        <input type="date" id="uiToDate" name="toDate" value="{{ $toDate }}" class="form-control"/>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-filter me-1"></i> Lọc dữ liệu
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.dashboard.revenue-report', ['fromDate' => $fromDate, 'toDate' => $toDate]) }}" class="text-decoration-none text-dark">
                    <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small fw-bold text-uppercase">Tổng doanh thu</div>
                            <h4 class="fw-bold mt-2 mb-0">{{ n0($totalRevenue) }} đ</h4>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.dashboard.don-hang-report', ['fromDate' => $fromDate, 'toDate' => $toDate]) }}" class="text-decoration-none text-dark">
                    <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small fw-bold text-uppercase">Tổng đơn hàng</div>
                            <h4 class="fw-bold mt-2 mb-0">{{ $totalOrders }}</h4>
                            <div class="small text-muted mt-1">{{ $pendingOrders }} đơn đang chờ xác nhận</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.dashboard.customers-report') }}" class="text-decoration-none text-dark">
                    <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small fw-bold text-uppercase">Tổng khách hàng</div>
                            <h4 class="fw-bold mt-2 mb-0">{{ $totalCustomers }}</h4>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.dashboard.san-pham-report', ['fromDate' => $fromDate, 'toDate' => $toDate]) }}" class="text-decoration-none text-dark">
                    <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small fw-bold text-uppercase">Sản Phẩm</div>
                            <h4 class="fw-bold mt-2 mb-0">{{ $totalProducts }}</h4>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Biểu đồ doanh thu theo ngày</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        $(function () {
            $.getJSON(@json(route('admin.dashboard.revenue-by-day')), {
                fromDate: @json($fromDate),
                toDate: @json($toDate)
            }, function (res) {
                new Chart(document.getElementById('revenueChart'), {
                    type: 'line',
                    data: {
                        labels: res.map(x => x.Date),
                        datasets: [{
                            label: 'Doanh thu (VNĐ)',
                            data: res.map(x => x.Total),
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => v.toLocaleString('vi-VN') + ' đ'
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            });
        });
    </script>
@endsection
