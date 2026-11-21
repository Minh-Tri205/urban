@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <span>Sản phẩm</span>
    </div>

    <!-- Content -->
    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h1>Quản lý Sản phẩm</h1>
                <p class="text-muted mt-1">Danh sách tất cả sản phẩm trong hệ thống ({{ $products->total() }})</p>
            </div>
            <a href="{{ route('admin.product.create') }}" class="btn btn-primary">
                <svg style="width: 16px; height: 16px; margin-right: 0.5rem;" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thêm sản phẩm
            </a>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">Tìm kiếm & Lọc</h3>
            </div>
            <div class="card-content">
                <form method="get" action="{{ route('admin.product.index') }}" id="filterForm">
                    <div class="grid grid-cols-3">
                        <input name="search" type="search" id="searchInput" class="form-control"
                               placeholder="Tìm kiếm theo tên sản phẩm..." value="{{ $search }}">

                        <select name="categoryId" id="categoryFilter" class="form-control">
                            <option value="">Tất cả danh mục</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->CategoryID }}" @selected((string) $category->CategoryID === (string) $categoryId)>{{ $category->CategoryName }}</option>
                            @endforeach
                        </select>

                        <select name="status" id="statusFilter" class="form-control">
                            <option value="">Tất cả trạng thái</option>
                            <option value="true" @selected($status === 'true')>Còn hàng</option>
                            <option value="false" @selected($status === 'false')>Hết hàng</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mt-3">Lọc</button>
                    <a href="{{ route('admin.product.index') }}" class="btn btn-outline btn-sm mt-3">Xóa bộ lọc</a>
                </form>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div id="bulkActionsBar" class="card mb-6 hidden" style="background-color: var(--accent);">
            <div class="card-content">
                <div class="d-flex flex-column gap-3">
                    <span><strong><span id="selectedCount">0</span></strong> sản phẩm đã chọn</span>
                    <div class="d-flex gap-3 align-items-center">
                        <div>Trạng thái:</div>
                        <label class="form-check p-0">
                            <input type="radio" name="bulkStatus" checked="checked" value="true"/>
                            Còn hàng
                        </label>
                        <label class="form-check">
                            <input type="radio" name="bulkStatus" value="false"/>
                            Hết hàng
                        </label>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" onclick="bulkUpdateStatus()">Cập nhật</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmBulkDelete()">Xóa</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-content" style="padding: 0;">
                <div class="table-container">
                    <table class="table m-0" id="dataTable">
                        <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAll" class="checkbox" aria-label="Chọn tất cả">
                            </th>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th style="width: 150px;">Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td><input data-id="{{ $product->ProductID }}" type="checkbox" class="checkbox row-checkbox" aria-label="Chọn sản phẩm"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img title="{{ $product->ProductName }}" src="{{ $product->primaryImageUrl() }}" alt="{{ $product->ProductID }}"
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius);">
                                        <div>
                                            <strong>{{ $product->ProductName }}</strong>
                                            <p class="text-description mb-0" style="font-size: 12px;">{{ \Illuminate\Support\Str::limit($product->Description, 80) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $product->category?->CategoryName }}</td>
                                <td><strong>{{ n0($product->BasePrice) }} đ</strong></td>
                                <td>{{ (int) $product->total_stock }}</td>
                                <td>
                                    <button type="button" class="badge badge-{{ $product->IsActive ? 'success' : 'danger' }} border-0"
                                            title="Bấm để đổi trạng thái"
                                            onclick="changeStatus({{ $product->ProductID }}, {{ $product->IsActive ? 'false' : 'true' }})">
                                        {{ $product->IsActive ? 'Còn hàng' : 'Hết hàng' }}
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-outline btn-sm" href="{{ route('admin.product.edit', $product->ProductID) }}">Sửa</a>
                                        <button type="button" class="btn btn-danger btn-sm"
                                                onclick="confirmDelete({{ $product->ProductID }}, {{ \Illuminate\Support\Js::from($product->ProductName) }})">
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div>Không tìm thấy sản phẩm nào.</div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @include('admin.partials.pagination', ['paginator' => $products])
    </div>
@endsection

@section('forms')
    <form action="{{ route('admin.product.edit-status') }}" method="post" class="d-none" id="form-change-status">
        @csrf
        <input type="hidden" name="status">
        <input type="hidden" name="productId">
    </form>

    <form action="{{ route('admin.product.delete') }}" method="post" class="d-none" id="form-delete">
        @csrf
        <input type="hidden" name="id">
    </form>
@endsection

@section('scripts')
    <script>
        const statusForm = document.getElementById('form-change-status');
        const deleteForm = document.getElementById('form-delete');

        function selectedIds() {
            return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.dataset.id).join(',');
        }

        function submitOnce(form) {
            if (form.dataset.submitted) return;
            form.dataset.submitted = '1';
            form.submit();
        }

        function changeStatus(id, status) {
            statusForm.action = @json(route('admin.product.edit-status'));
            statusForm.elements['productId'].value = id;
            statusForm.elements['status'].value = status;
            submitOnce(statusForm);
        }

        function bulkUpdateStatus() {
            statusForm.action = @json(route('admin.product.edit-many-status'));
            statusForm.elements['productId'].value = selectedIds();
            statusForm.elements['status'].value = document.querySelector('input[name="bulkStatus"]:checked').value;
            submitOnce(statusForm);
        }

        function confirmDelete(id, name) {
            if (confirm(`Bạn có chắc chắn muốn xóa sản phẩm "${name}"?`)) {
                deleteForm.action = @json(route('admin.product.delete'));
                deleteForm.elements['id'].value = id;
                submitOnce(deleteForm);
            }
        }

        function confirmBulkDelete() {
            if (confirm('Bạn có chắc chắn muốn xóa những sản phẩm đang chọn?')) {
                deleteForm.action = @json(route('admin.product.delete-many'));
                deleteForm.elements['id'].value = selectedIds();
                submitOnce(deleteForm);
            }
        }
    </script>
@endsection
