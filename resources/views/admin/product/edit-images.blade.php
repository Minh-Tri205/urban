@extends('layouts.admin')

@section('title', 'Chỉnh sửa hình ảnh')

@section('content')
    @php
        $primary = $product->primaryImage();
        $additional = $product->productImages->reject(fn ($img) => $primary && $img->ImageID === $primary->ImageID);
        $remaining = max(0, $maxAdditional - $additional->count());
    @endphp

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('admin.product.index') }}">Sản phẩm</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('admin.product.edit', $product->ProductID) }}">Sửa sản phẩm</a>
        <span class="breadcrumb-separator">/</span>
        <span>Quản lý hình ảnh</span>
    </div>

    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div class="d-flex align-items-center gap-4">
                <a href="{{ route('admin.product.edit', $product->ProductID) }}" class="btn btn-icon btn-outline" aria-label="Quay lại">
                    <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <div>
                    <h1>Quản lý hình ảnh: <span>{{ $product->ProductName }}</span></h1>
                    <p class="text-muted mt-1">Cập nhật ảnh chính và ảnh phụ</p>
                </div>
            </div>
        </div>

        @include('admin.partials.errors')

        <form action="{{ route('admin.product.update-images', $product->ProductID) }}" method="post" enctype="multipart/form-data" id="imageForm">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ảnh chính</h3>
                    </div>
                    <div class="card-content">
                        @include('admin.product.partials.image-picker', [
                            'name' => 'MainImage', 'inputId' => 'mainImage', 'previewId' => 'mainImagePreview',
                            'label' => 'Ảnh hiện tại (bấm để thay)', 'maxFiles' => 1,
                            'current' => $primary ? $primary->url() : null,
                        ])
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ảnh phụ</h3>
                    </div>
                    <div class="card-content">
                        <div class="form-group">
                            <label class="form-label">Ảnh hiện có ({{ $additional->count() }}/{{ $maxAdditional }} ảnh)</label>
                            <div id="existingAdditionalImages" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                @foreach ($additional as $img)
                                    <div style="position: relative; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden;">
                                        <img src="{{ $img->url() }}" alt="Ảnh phụ" style="width: 100%; height: 150px; object-fit: cover;">
                                        <button type="button" class="btn btn-danger"
                                                style="position: absolute; top: 4px; right: 4px; padding: 0.25rem 0.5rem; font-size: 11px;"
                                                onclick="removeImage({{ $img->ImageID }})">
                                            Xóa
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @if ($additional->isEmpty())
                                <p class="text-muted mt-2" style="font-size: 13px;">Chưa có ảnh phụ nào</p>
                            @endif
                        </div>

                        @if ($remaining > 0)
                            @include('admin.product.partials.image-picker', [
                                'name' => 'AdditionalImages[]', 'inputId' => 'additionalImages', 'previewId' => 'additionalImagesPreview',
                                'label' => "Thêm ảnh phụ mới (còn {$remaining}/{$maxAdditional} chỗ)", 'multiple' => true, 'maxFiles' => $remaining,
                                'hint' => "Chọn tối đa {$remaining} ảnh (PNG, JPG, WEBP)",
                            ])
                        @else
                            <div class="alert alert-info mt-3 mb-0">
                                Đã đạt giới hạn {{ $maxAdditional }} ảnh phụ. Vui lòng xóa ảnh cũ để thêm ảnh mới.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-content p-0 mt-4">
                <div class="d-flex justify-content-end align-items-center gap-3">
                    <a href="{{ route('admin.product.edit', $product->ProductID) }}" class="btn btn-outline">Hủy</a>
                    <button type="submit" class="btn btn-primary">Cập nhật hình ảnh</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('forms')
    <form action="{{ route('admin.product.delete-image') }}" method="post" class="d-none" id="deleteImageForm">
        @csrf
        <input type="hidden" name="productId" value="{{ $product->ProductID }}">
        <input type="hidden" name="imageId" value="">
    </form>
@endsection

@section('scripts')
    <script src="{{ asset('Content/js/Admin/product-form.js') }}"></script>
    <script>
        setupProductForm('imageForm', false);

        function removeImage(imageId) {
            if (confirm('Bạn có chắc muốn xóa ảnh này không?')) {
                const form = document.getElementById('deleteImageForm');
                form.elements['imageId'].value = imageId;
                form.submit();
            }
        }
    </script>
@endsection
