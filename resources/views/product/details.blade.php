@extends('layouts.app')

@section('title', $product->ProductName)

@section('content')
    @php
        $primaryImage = $product->primaryImage();
        $thumbnails = $product->productImages->reject(fn ($image) => $primaryImage && $image->ImageID === $primaryImage->ImageID);
    @endphp

    <section id="product-detail" class="single-product no-padding-top"
             style="height: calc(100vh - 100px); padding-bottom: 100px; overflow: hidden">
        <div class="container">
            <div class="row g-5 py-1">
                <div class="col-md-7">
                    <div class="row h-100">
                        <div class="col-md-3 d-none d-md-block pb-2">
                            <div>
                                <div style="overflow-y: auto; max-height: calc(100vh);">
                                    @foreach ($thumbnails as $image)
                                        <div class="pb-2">
                                            <img src="{{ $image->url() }}" alt="image" class="thumb-image img-fluid">
                                        </div>
                                    @endforeach
                                </div>
                                <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span></div>
                        </div>
                        <div class="col-md-9">
                            <div>
                                <div>
                                    <div>
                                        <img src="{{ $product->primaryImageUrl() }}" alt="product-large" class="img-fluid">
                                    </div>
                                </div>
                                <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">

                    <div class="mb-3">
                        <a href="{{ route('product.index') }}" class="">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                    <div class="product-info" style="font-size: 14px">
                        <!-- Tiêu đề sản phẩm -->
                        <h1 class="product-title text-uppercase"
                            style="padding: 0;margin:0;font-size: 1.8rem; letter-spacing: 1px;">
                            {{ $product->ProductName }}
                        </h1>

                        <!-- Giá sản phẩm -->
                        <div class="product-price ">
                            <span class="fs-2">{{ vnd($product->BasePrice) }}</span>
                        </div>

                        <!-- Mô tả -->
                        <p class="text-muted mb-4 lh-lg">
                            {{ $product->Description }}
                        </p>
                        <!-- Product Details -->
                        <div class="border rounded p-3 bg-light">
                            <div class="row g-3 small">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-upc-scan text-primary fs-5 me-2"></i>
                                        <div>
                                            <div class="text-muted">Trạng thái</div>
                                            @if ($product->IsActive)
                                                <strong>Còn hàng</strong>
                                            @else
                                                <strong class="text-danger">Hết hàng</strong>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-tag text-primary fs-5 me-2"></i>
                                        <div>
                                            <div class="text-muted">Danh mục</div>
                                            <strong>{{ $product->category?->CategoryName }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-truck text-primary fs-5 me-2"></i>
                                        <div>
                                            <div class="text-muted">Giao hàng</div>
                                            <strong>2-3 ngày</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-arrow-repeat text-primary fs-5 me-2"></i>
                                        <div>
                                            <div class="text-muted">Đổi trả</div>
                                            <strong>30 ngày</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="mt-3">
                            <span class="text-muted small me-2">Tags:</span>
                            @if ($product->category?->categoryGroup)
                                <a href="#" class="badge text-bg-secondary text-decoration-none me-1">{{ $product->category->categoryGroup->GroupName }}</a>
                            @endif
                            @if ($product->category)
                                <a href="{{ route('product.index', ['catId' => $product->CategoryID]) }}"
                                   class="badge text-bg-secondary text-decoration-none me-1">{{ $product->category->CategoryName }}</a>
                            @endif
                        </div>

                        <hr class="my-4">

                        @if ($product->IsActive)
                            <form action="{{ route('cart.add') }}" method="post">
                                @csrf
                                <input id="ProductID" name="ProductID" type="hidden" value="{{ $product->ProductID }}">
                                <div class="row">

                                    <!-- Chọn Màu-->
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-bold mb-2" for="ColorId">Màu sắc:</label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <select class="form-control" id="ColorId" name="ColorId" required="required">
                                                <option value="">Chọn màu sắc</option>
                                                @foreach ($colors as $color)
                                                    <option value="{{ $color->ColorID }}">{{ $color->ColorName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- Chọn Size -->
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-bold mb-2" for="SizeId">Chọn kích thước:</label>
                                        <div class="btn-group w-100" role="group">
                                            <select class="form-control" id="SizeId" name="SizeId" required="required">
                                                <option value="">Chọn size</option>
                                                @foreach ($sizes as $size)
                                                    <option value="{{ $size->SizeID }}">{{ $size->SizeName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-4">

                                    <!-- Số lượng -->
                                    <label class="form-label fw-bold" for="quantity">Số lượng:</label>
                                    <div>
                                        <div class="input-group ">
                                            <input type="number" id="quantity" name="Quantity"
                                                   class="form-control text-center fw-bold"
                                                   value="1" min="1" max="100">
                                        </div>
                                    </div>

                                    <!-- Thêm vào giỏ hàng -->
                                    <button type="submit" class="btn btn-black btn-rounded-none fw-bold flex-grow-1 ">
                                        <i class="bi bi-plus-lg me-2"></i>Thêm vào giỏ
                                    </button>
                                </div>
                            </form>
                        @else
                            <button disabled="" type="button"
                                    class="btn btn-black btn-rounded-none fw-bold flex-grow-1 w-100 ">
                                <i class="bi bi-lightning-charge me-2"></i> Hết hàng
                            </button>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <!-- Section Sản phẩm liên quan -->
        <section id="featured-products" class="product-store position-relative py-5 ">
            <div class="container">
                <div class="row">
                    <div class="display-header pb-3 d-md-flex justify-content-between align-items-center flex-wrap col-md-12">
                        <h2 class="display-2 text-dark text-capitalize fs-3">Sản phẩm liên quan</h2>
                        <a href="{{ route('product.index') }}" class="btn btn-arrow outline-dark position-relative">
                            <span class="text-capitalize pe-1">Xem tất cả</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div id="featured-swiper" class="product-swiper col-md-12">
                        <div class="swiper">
                            <div class="swiper-wrapper">
                                @foreach ($relatedProducts as $related)
                                    @include('partials.product-card', ['product' => $related])
                                @endforeach
                            </div>
                        </div>
                        <div class="swiper-pagination text-center mt-5"></div>
                    </div>

                </div>
            </div>
        </section>
    @endif
@endsection
