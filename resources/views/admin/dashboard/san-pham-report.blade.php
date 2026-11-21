@extends('layouts.admin')

@section('title', 'Báo cáo hiệu suất sản phẩm')

@section('content')
    <div class="container-fluid mt-4">
        <div class="mb-2">
            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-link btn-sm text-decoration-none text-muted p-0">
                <i class="fas fa-long-arrow-alt-left me-1"></i> Quay lại
            </a>
        </div>
        <div class="mb-4">
            <h3 class="fw-bold text-dark"><i class="fas fa-box-open me-2"></i>BÁO CÁO CHI TIẾT SẢN PHẨM</h3>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label small fw-bold text-muted" for="reportFromDate">Từ ngày</label>
                        <input type="date" id="reportFromDate" name="fromDate" value="{{ $fromDate }}" class="form-control"/>
                    </div>
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label small fw-bold text-muted" for="reportToDate">Đến ngày</label>
                        <input type="date" id="reportToDate" name="toDate" value="{{ $toDate }}" class="form-control"/>
                    </div>
                    <div class="col-sm-4 col-md-2">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="fas fa-filter me-1"></i> Lọc dữ liệu
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Xu hướng tổng số lượng bán ra</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="totalQtyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-success"><i class="fas fa-chart-bar me-2"></i>Phân bổ loại sản phẩm bán ra theo thời gian</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 450px;">
                            <canvas id="productBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list-ol me-2"></i>Thống kê chi tiết từng sản phẩm</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Sản phẩm</th>
                                    <th style="width: 40%">Tỷ trọng doanh số (%)</th>
                                    <th class="text-center">Số lượng đã bán</th>
                                    <th class="text-center">Tồn kho hiện tại</th>
                                </tr>
                                </thead>
                                <tbody id="productTableBody"></tbody>
                            </table>
                        </div>
                        <div class="text-center py-3 border-top" id="btnContainer" style="display:none;">
                            <button class="btn btn-outline-primary btn-sm px-5 fw-bold" type="button" onclick="toggleView()">
                                <i class="fas fa-chevron-down me-1"></i> Xem tất cả sản phẩm
                            </button>
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
        const params = { fromDate: @json($fromDate), toDate: @json($toDate) };
        const chartColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#6f42c1', '#fd7e14', '#20c997'];
        let fullProductData = [];
        let isShowingAll = false;

        $(function () {
            $.getJSON(@json(route('admin.dashboard.get-total-qty-trend')), params, function (res) {
                new Chart(document.getElementById('totalQtyChart'), {
                    type: 'line',
                    data: {
                        labels: res.map(x => x.Date),
                        datasets: [{
                            label: 'Số lượng bán',
                            data: res.map(x => x.Qty),
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: { maintainAspectRatio: false }
                });
            });

            $.getJSON(@json(route('admin.dashboard.get-product-breakdown')), params, function (res) {
                new Chart(document.getElementById('productBarChart'), {
                    type: 'bar',
                    data: {
                        labels: res.labels,
                        datasets: res.datasets.map((ds, i) => ({
                            label: ds.label,
                            data: ds.data,
                            backgroundColor: chartColors[i % chartColors.length]
                        }))
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: { x: { stacked: true }, y: { stacked: true } }
                    }
                });
            });

            $.getJSON(@json(route('admin.dashboard.get-all-products-report')), params, function (res) {
                fullProductData = res;
                renderTable();
            });
        });

        function renderTable() {
            const limit = isShowingAll ? fullProductData.length : 5;
            let html = '';

            if (!fullProductData || fullProductData.length === 0) {
                html = '<tr><td colspan="4" class="text-center py-4 text-muted">Không có dữ liệu trong khoảng thời gian này</td></tr>';
                $('#btnContainer').hide();
            } else {
                $('#btnContainer').toggle(fullProductData.length > 5);
                fullProductData.slice(0, limit).forEach(item => {
                    // Cảnh báo nếu tồn kho thấp (dưới 10)
                    const stockClass = item.Stock < 10 ? 'text-danger fw-bold' : 'text-muted';

                    html += `<tr>
                        <td class="ps-4 fw-bold text-dark">${escapeHtml(item.Name)}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-2 small fw-bold">${item.Percentage}%</span>
                                <div class="progress flex-grow-1" style="height: 10px;">
                                    <div class="progress-bar bg-info shadow-sm" style="width: ${item.Percentage}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center fw-bold text-primary">${item.Qty.toLocaleString('vi-VN')}</td>
                        <td class="text-center ${stockClass}">${item.Stock.toLocaleString('vi-VN')}</td>
                    </tr>`;
                });
            }

            $('#productTableBody').html(html);
            $('#btnContainer button').html(isShowingAll
                ? '<i class="fas fa-chevron-up me-1"></i> Thu gọn danh sách'
                : '<i class="fas fa-chevron-down me-1"></i> Xem tất cả sản phẩm');
        }

        function toggleView() {
            isShowingAll = !isShowingAll;
            renderTable();
        }
    </script>
@endsection
