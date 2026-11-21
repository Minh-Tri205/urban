@extends('layouts.admin')

@section('title', 'Chỉnh sửa sản phẩm')

@section('styles')
    <link rel="stylesheet" href="{{ asset('Content/css/Admin/product-add.css') }}"/>
@endsection

@section('content')
    @php $isActive = old('IsActive', $product->IsActive ? 'True' : 'False'); @endphp

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('admin.product.index') }}">Sản phẩm</a>
        <span class="breadcrumb-separator">/</span>
        <span>Sửa sản phẩm</span>
    </div>

    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div class="d-flex align-items-center gap-4">
                <a href="{{ route('admin.product.index') }}" class="btn btn-icon btn-outline" aria-label="Quay lại">
                    <svg fill="none" style="width: 20px; height: 20px;" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <div>
                    <h1>Sửa sản phẩm: <span>{{ $product->ProductName }}</span></h1>
                    <p class="text-muted mt-1">Cập nhật thông tin sản phẩm</p>
                </div>
            </div>
            <button type="button" class="btn btn-danger"
                    onclick="if (confirm({{ \Illuminate\Support\Js::from('Bạn có chắc chắn muốn xóa sản phẩm "' . $product->ProductName . '"?') }})) document.getElementById('form-delete').submit();">
                <svg style="width: 16px; height: 16px; margin-right: 0.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                Xóa sản phẩm
            </button>
        </div>

        @include('admin.partials.errors')

        <form action="{{ route('admin.product.update', $product->ProductID) }}" method="post" enctype="multipart/form-data" id="productForm">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                <!-- Left Column -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Thông tin cơ bản</h3>
                            <div>Mã sản phẩm: <strong>#{{ $product->ProductID }}</strong></div>
                        </div>
                        <div class="card-content">
                            <div class="form-group">
                                <label class="form-label" for="ProductName">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" id="ProductName" name="ProductName" class="form-control" required maxlength="200"
                                       placeholder="Nhập tên sản phẩm" value="{{ old('ProductName', $product->ProductName) }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="Description">Mô tả chi tiết</label>
                                <textarea id="Description" name="Description" class="form-control" rows="6"
                                          placeholder="Mô tả đầy đủ về sản phẩm, chất liệu, kiểu dáng...">{{ old('Description', $product->Description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Giá &amp; Khuyến mãi</h3>
                        </div>
                        <div class="card-content">
                            <div class="grid grid-cols-2">
                                <div class="form-group">
                                    <label class="form-label" for="BasePrice">Giá bán <span class="text-danger">*</span></label>
                                    <input value="{{ old('BasePrice', (int) $product->BasePrice) }}" placeholder="Nhập giá bán"
                                           type="number" id="BasePrice" name="BasePrice" class="form-control" required min="0">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="DiscountPercent">% Giảm giá</label>
                                    <input value="{{ old('DiscountPercent', (int) $product->DiscountPercent) }}" type="number"
                                           id="DiscountPercent" name="DiscountPercent" class="form-control"
                                           placeholder="Nhập % giảm giá" min="0" max="100" step="1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Biến thể sản phẩm</h3>
                            <div class="text-muted" style="margin-top: 0.5rem;">Cập nhật SKU, tồn kho và trạng thái của từng biến thể</div>
                        </div>
                        <div class="card-content">
                            <div style="overflow-x:auto;">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>Màu sắc</th>
                                        <th>Size</th>
                                        <th>SKU</th>
                                        <th>Tồn kho</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($product->productVariants->sortBy(['ColorID', 'SizeID'])->values() as $index => $variant)
                                        @php $status = old("Variants.$index.Status", $variant->Status); @endphp
                                        <tr>
                                            <td>
                                                <span class="swatch" title="{{ $variant->color?->ColorName }}" style="background: {{ $variant->color?->HexCode }}"></span>
                                                <small class="d-block text-muted">{{ $variant->color?->ColorName }}</small>
                                                <input name="Variants[{{ $index }}][VariantID]" value="{{ $variant->VariantID }}" type="hidden">
                                            </td>
                                            <td><strong>{{ $variant->size?->SizeName }}</strong></td>
                                            <td>
                                                <input name="Variants[{{ $index }}][SKU]" type="text" class="form-control" maxlength="50"
                                                       value="{{ old("Variants.$index.SKU", $variant->SKU) }}" placeholder="Auto" style="max-width: 150px">
                                            </td>
                                            <td>
                                                <input name="Variants[{{ $index }}][Stock]" type="number" class="form-control" required
                                                       value="{{ old("Variants.$index.Stock", $variant->Stock) }}" min="0" style="width:100px;">
                                            </td>
                                            <td>
                                                <select class="form-control" name="Variants[{{ $index }}][Status]">
                                                    <option value="Available" @selected($status === 'Available')>Còn hàng</option>
                                                    <option value="OutOfStock" @selected($status === 'OutOfStock')>Hết hàng</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top:1rem; padding:1rem; background:var(--muted); border-radius:var(--radius);">
                                <p style="font-size:12px; color:var(--muted-foreground); margin:0;">
                                    <strong>Tổng: {{ $product->productVariants->count() }} biến thể · {{ $product->productVariants->sum('Stock') }} sản phẩm trong kho</strong><br>
                                    SKU sẽ tự động tạo nếu để trống.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-content p-0">
                        <div class="d-flex justify-content-end align-items-center gap-3">
                            <a class="btn btn-outline" href="{{ route('admin.product.index') }}">Hủy</a>
                            <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title m-0">Hình ảnh</h3>
                            <a class="btn btn-outline" href="{{ route('admin.product.edit-images', $product->ProductID) }}">Chi tiết</a>
                        </div>
                        <div class="card-content">
                            @include('admin.product.partials.image-picker', [
                                'name' => 'MainImage', 'inputId' => 'mainImage', 'previewId' => 'mainImagePreview',
                                'label' => 'Ảnh chính (bấm để thay)', 'maxFiles' => 1,
                                'current' => $product->primaryImage() ? $product->primaryImageUrl() : null,
                            ])
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Trạng thái</h3>
                        </div>
                        <div class="card-content">
                            <div class="form-group">
                                <label class="form-label" for="IsActive">Trạng thái sản phẩm <span class="text-danger">*</span></label>
                                <select id="IsActive" name="IsActive" class="form-control" required>
                                    <option value="True" @selected($isActive === 'True')>Còn hàng</option>
                                    <option value="False" @selected($isActive === 'False')>Hết hàng</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Phân loại</h3>
                        </div>
                        <div class="card-content">
                            <div class="form-group">
                                <label class="form-label" for="CategoryID">Danh mục <span class="text-danger">*</span></label>
                                <select id="CategoryID" name="CategoryID" class="form-control" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->CategoryID }}" @selected((string) old('CategoryID', $product->CategoryID) === (string) $category->CategoryID)>
                                            {{ $category->categoryGroup?->GroupName }} / {{ $category->CategoryName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('forms')
    <form action="{{ route('admin.product.delete', $product->ProductID) }}" method="post" class="d-none" id="form-delete">
        @csrf
    </form>
@endsection

@section('scripts')
    <script src="{{ asset('Content/js/Admin/product-form.js') }}"></script>
    <script>
        setupProductForm('productForm', false);
    </script>
@endsection
