@extends('layouts.admin')

@section('title', 'Thêm sản phẩm')

@section('styles')
    <link rel="stylesheet" href="{{ asset('Content/css/Admin/product-add.css') }}"/>
@endsection

@section('content')
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('admin.product.index') }}">Sản phẩm</a>
        <span class="breadcrumb-separator">/</span>
        <span>Thêm sản phẩm</span>
    </div>

    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div class="d-flex align-items-center gap-4">
                <a href="{{ route('admin.product.index') }}" class="btn btn-icon btn-outline" aria-label="Quay lại">
                    <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <div>
                    <h1>Thêm sản phẩm mới</h1>
                    <p class="text-muted mt-1">Nhập thông tin sản phẩm mới vào hệ thống</p>
                </div>
            </div>
        </div>

        @include('admin.partials.errors')

        <form action="{{ route('admin.product.store') }}" method="post" enctype="multipart/form-data" id="productForm">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                <!-- Left Column -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin cơ bản</h3>
                        </div>
                        <div class="card-content">
                            <div class="form-group">
                                <label class="form-label" for="ProductName">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" id="ProductName" name="ProductName" class="form-control" required maxlength="200"
                                       placeholder="Nhập tên sản phẩm" value="{{ old('ProductName') }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="Description">Mô tả chi tiết</label>
                                <textarea id="Description" name="Description" class="form-control" rows="6"
                                          placeholder="Mô tả đầy đủ về sản phẩm, chất liệu, kiểu dáng...">{{ old('Description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Giá & Khuyến mãi</h3>
                        </div>
                        <div class="card-content">
                            <div class="grid grid-cols-2">
                                <div class="form-group">
                                    <label class="form-label" for="BasePrice">Giá bán <span class="text-danger">*</span></label>
                                    <input type="number" id="BasePrice" name="BasePrice" class="form-control" required
                                           value="{{ old('BasePrice', 299000) }}" min="0" step="1000">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="DiscountPercent">% Giảm giá</label>
                                    <input type="number" id="DiscountPercent" name="DiscountPercent" class="form-control"
                                           value="{{ old('DiscountPercent', 0) }}" min="0" max="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Biến thể sản phẩm</h3>
                            <div class="text-muted" style="margin-top: 0.5rem;">Chọn size và màu sắc, hệ thống tạo biến thể cho từng cặp màu × size</div>
                        </div>
                        <div class="card-content">
                            <div class="grid grid-cols-2">
                                <div class="form-group">
                                    <label class="form-label">Size</label>
                                    <div id="sizeGroup">
                                        @foreach ($sizes as $size)
                                            <label class="size-pill" data-value="{{ $size->SizeName }}">
                                                {{ $size->SizeName }}
                                                <input name="sizes" type="checkbox" class="chk-size" data-id="{{ $size->SizeID }}"
                                                       value="{{ $size->SizeName }}" onchange="updateVariants()" form="variantPicker">
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Màu</label>
                                    <div id="colorGroup" style="display:flex; flex-wrap:wrap; gap:12px;">
                                        @foreach ($colors as $color)
                                            <label title="{{ $color->ColorName }}">
                                                <span class="swatch {{ strtoupper((string) $color->HexCode) === '#FFFFFF' ? 'white-bg' : '' }}" style="background: {{ $color->HexCode }}">
                                                    <input name="colors" type="checkbox" class="chk-color" data-id="{{ $color->ColorID }}"
                                                           data-hex="{{ $color->HexCode }}" value="{{ $color->ColorName }}"
                                                           onchange="updateVariants()" form="variantPicker">
                                                    <span class="check">✓</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div id="variantsContainer">
                                <div style="text-align: center; padding: 2rem; color: var(--muted-foreground);">
                                    <p>Chọn màu sắc và kích thước để tạo biến thể</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-content p-0">
                        <div class="d-flex justify-content-end align-items-center gap-3">
                            <a href="{{ route('admin.product.index') }}" class="btn btn-outline">Hủy</a>
                            <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Trạng thái</h3>
                        </div>
                        <div class="card-content">
                            <div class="form-group">
                                <label class="form-label" for="IsActive">Trạng thái sản phẩm <span class="text-danger">*</span></label>
                                <select id="IsActive" name="IsActive" class="form-control" required>
                                    <option value="True" @selected(old('IsActive', 'True') === 'True')>Còn hàng</option>
                                    <option value="False" @selected(old('IsActive') === 'False')>Hết hàng</option>
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
                                    <option value="">Chọn danh mục</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->CategoryID }}" @selected((string) old('CategoryID') === (string) $category->CategoryID)>
                                            {{ $category->categoryGroup?->GroupName }} / {{ $category->CategoryName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Hình ảnh</h3>
                        </div>
                        <div class="card-content">
                            @include('admin.product.partials.image-picker', [
                                'name' => 'MainImage', 'inputId' => 'mainImage', 'previewId' => 'mainImagePreview',
                                'label' => 'Ảnh chính <span class="text-danger">*</span>', 'required' => true, 'maxFiles' => 1,
                            ])
                            @include('admin.product.partials.image-picker', [
                                'name' => 'AdditionalImages[]', 'inputId' => 'additionalImages', 'previewId' => 'additionalImagesPreview',
                                'label' => 'Ảnh phụ', 'multiple' => true, 'maxFiles' => $maxAdditional ?? \App\Services\ProductImageStorage::MAX_ADDITIONAL_IMAGES,
                                'hint' => 'Tối đa ' . \App\Services\ProductImageStorage::MAX_ADDITIONAL_IMAGES . ' ảnh, mỗi ảnh tối đa 5MB',
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </form>
        {{-- Ô chọn màu / size không gửi lên server, chỉ dùng để tạo bảng biến thể --}}
        <form id="variantPicker" onsubmit="return false;"></form>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('Content/js/Admin/product-form.js') }}"></script>
    <script>
        setupProductForm('productForm', true);
    </script>
@endsection
