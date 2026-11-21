@extends('layouts.admin')

@section('title', 'Phân tích khách hàng chuyên sâu')

@section('content')
    <div class="container-fluid mt-4">

        <div class="mb-2">
            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-link btn-sm text-decoration-none text-muted p-0">
                <i class="fas fa-long-arrow-alt-left me-1"></i> Quay lại
            </a>
        </div>

        <div class="mb-4">
            <h4 class="fw-bold text-dark d-flex align-items-center">
                <span class="badge bg-primary-subtle text-primary me-2 p-2">
                    <i class="fas fa-user-friends"></i>
                </span>
                PHÂN TÍCH CHI TIÊU & GIỮ CHÂN KHÁCH HÀNG
            </h4>
        </div>

        {{-- Widget thống kê nhanh (khách đăng ký trong khoảng ngày đã chọn) --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-start border-primary border-4 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="small fw-bold text-primary text-uppercase mb-1">Khách đăng ký mới</div>
                        <div class="h5 mb-0 fw-bold">{{ $customers->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-warning border-4 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="small fw-bold text-warning text-uppercase mb-1">Khách tiềm năng (Chưa mua)</div>
                        <div class="h5 mb-0 fw-bold">{{ $customers->where('completed_orders', 0)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.partials.date-filter', ['label' => 'Lọc dữ liệu'])

        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Biến động chi tiêu theo ngày</h6></div>
                    <div class="card-body" style="height:300px"><canvas id="spendingChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 fw-bold text-info">Tỷ lệ khách quay lại (Retention)</h6></div>
                    <div class="card-body" style="height:300px"><canvas id="retentionChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-warning text-dark"><h6 class="m-0 fw-bold">🏆 Top 10 khách hàng chi tiêu nhiều nhất</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th class="ps-3">Tài khoản</th><th class="text-end pe-3">Tổng chi tiêu</th></tr></thead>
                            <tbody id="topSpendingBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 fw-bold text-dark">Danh sách chi tiết & Giá trị vòng đời (CLV)</h6></div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Tài khoản</th>
                                <th>Email</th>
                                <th class="text-center">Số đơn</th>
                                <th class="text-end pe-3">Tổng chi tiêu (CLV)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($customers as $c)
                                @php
                                    $allOrders = (int) $c->all_orders;
                                    $completedOrders = (int) $c->completed_orders;
                                    $totalSpent = (float) $c->total_spent;
                                    [$rowClass, $badgeClass, $badgeText] = match (true) {
                                        $totalSpent > 1000000 => ['table-success', 'bg-danger', 'Đại gia'],
                                        $completedOrders >= 2 => ['table-success', 'bg-success', 'Trung thành'],
                                        $allOrders >= 2 && $completedOrders === 1 => ['table-warning', 'bg-warning text-dark', 'Tiềm năng'],
                                        $allOrders === 0 => ['table-light text-danger', 'bg-secondary', 'Chưa mua hàng'],
                                        default => ['', null, null],
                                    };
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="ps-3">
                                        {{ $c->UserName }}
                                        @if ($badgeText)
                                            <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $c->Email }}</td>
                                    <td class="text-center">{{ $completedOrders }} / {{ $allOrders }} đơn</td>
                                    <td class="text-end fw-bold text-danger pe-3">{{ n0($totalSpent) }} đ</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Không có khách hàng đăng ký trong khoảng thời gian này</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const range = { fromDate: @json($fromDate), toDate: @json($toDate) };

        // 1. Biểu đồ chi tiêu theo ngày
        $.getJSON(@json(route('admin.dashboard.get-customer-spending-by-day')), range, function (data) {
            new Chart(document.getElementById('spendingChart'), {
                type: 'bar',
                data: {
                    labels: data.map(x => x.Date),
                    datasets: [{ label: 'VNĐ', data: data.map(x => x.Total), backgroundColor: '#4e73df' }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });

        // 2. Biểu đồ tỷ lệ quay lại
        $.getJSON(@json(route('admin.dashboard.get-customer-retention')), function (data) {
            new Chart(document.getElementById('retentionChart'), {
                type: 'doughnut',
                data: {
                    labels: data.map(x => x.label),
                    datasets: [{ data: data.map(x => x.value), backgroundColor: data.map(x => x.color) }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
                }
            });
        });

        // 3. Bảng Top 10 khách hàng
        $.getJSON(@json(route('admin.dashboard.get-top10-spending-customers')), function (data) {
            const html = data.map(item => `<tr>
                <td class="fw-bold ps-3">${escapeHtml(item.Name)}</td>
                <td class="text-end text-danger fw-bold pe-3">${item.TotalSpent.toLocaleString('vi-VN')} đ</td>
            </tr>`).join('');
            $('#topSpendingBody').html(html || '<tr><td colspan="2" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>');
        });
    </script>
@endsection
