{{-- Port từ Views/Shared/_Product.cshtml. Biến: $product (App\Models\Product, nên eager load productImages) --}}
<div class="swiper-slide">
    <div class="product-card image-zoom-effect link-effect d-flex flex-wrap">
        <div class="image-holder">
            <a href="{{ route('product.details', $product->ProductID) }}">
                <img style="min-height: 100%"
                     src="{{ $product->primaryImageUrl() }}"
                     alt="product-item" class="product-image img-fluid">
            </a>

        </div>
        <div class="cart-concern opacity-100">
            <h3 class="card-title text-capitalize pt-3 ">
                <a href="{{ route('product.details', $product->ProductID) }}">{{ $product->ProductName }}</a>
            </h3>
            <div class="cart-info">

                <a class="fw-bold" href="{{ route('product.details', $product->ProductID) }}"
                   data-after="Xem chi tiết">
                    <span>{{ vnd($product->BasePrice) }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
