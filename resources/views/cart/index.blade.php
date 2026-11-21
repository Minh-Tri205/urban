@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('styles')
    <style>
        /* XỬ LÝ INPUT NUMBER */
        .quantity-input {
            border-left: 0;
            border-right: 0;
            font-weight: 600;
        }

        .quantity-input:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }

        /* XÓA ARROW CỦA INPUT NUMBER */
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .quantity-input[type=number] {
            -moz-appearance: textfield;
        }

        /* HOVER NÚT +/- */
        .btn-increase:hover,
        .btn-decrease:hover {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        /* ANIMATION CHO CART ITEM */
        .cart-item {
            transition: all 0.3s ease;
        }
    </style>
@endsection

@section('content')
    <section id="cart" style="min-height: 70vh;">
        <div class="container">
            @if ($items->isEmpty())
                <div class="d-flex align-items-center justify-content-center" style="height: calc(100vh - 150px)">
                    <div class="text-center">
                        <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">Giỏ hàng trống</h4>
                        <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
                        <a href="{{ route('product.index') }}" class="btn btn-primary btn-rounded-none mt-3">
                            <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            @else
                <div class="mb-3">
                    <a href="{{ route('product.index') }}" class="">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
                <div class="row">
                    <!-- Cart Items -->
                    <div class="col-lg-8 col-md-12 mb-5">
                        <!-- Page Title -->
                        <div class="mb-5">
                            <h1 class="display-6 mb-2">Giỏ hàng của bạn</h1>
                            <p class="text-muted fs-5">Bạn có <span class="text-dark fw-bold">{{ $items->count() }}</span> sản phẩm trong giỏ hàng</p>
                        </div>
                        <div class="cart-table">
                            <!-- Header -->
                            <div class="cart-header">
                                <div class="row">
                                    <div class="col-5">
                                        <h6 class="text-uppercase text-muted">Sản phẩm</h6>
                                    </div>
                                    <div class="col-2 text-center">
                                        <h6 class="text-uppercase text-muted">Số lượng</h6>
                                    </div>
                                    <div class="col-3 text-end">
                                        <h6 class="text-uppercase text-muted">Thành tiền</h6>
                                    </div>
                                    <div class="col-2 text-end">
                                        <h6 class="text-uppercase text-muted">Thao tác</h6>
                                    </div>
                                </div>
                            </div>

                            <!-- Cart Items Loop -->
                            @foreach ($items as $cartItem)
                                @php
                                    $variant = $cartItem->productVariant;
                                    $product = $variant?->product;
                                    $itemPrice = (float) ($product?->BasePrice ?? 0);
                                    $itemTotal = $itemPrice * $cartItem->Quantity;
                                @endphp

                                <div class="cart-item border-top py-4" data-variant-id="{{ $cartItem->VariantID }}">
                                    <div class="row align-items-center">
                                        <!-- Product Info -->
                                        <div class="col-lg-5 col-md-5 mb-3 mb-lg-0">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3" style="width: 100px; height: 100px;">
                                                    <img src="{{ $product ? $product->primaryImageUrl() : asset('Content/img/products/default.jpg') }}"
                                                         alt="{{ $product?->ProductName }}"
                                                         class="img-fluid rounded"
                                                         style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fs-5">
                                                        @if ($product)
                                                            <a href="{{ route('product.details', $product->ProductID) }}"
                                                               class="text-decoration-none text-dark fw-bold">
                                                                {{ $product->ProductName }}
                                                            </a>
                                                        @endif
                                                    </h6>
                                                    <div class="small text-muted mb-0 fs-6">
                                                        <div class="text-dark me-1">
                                                            Màu: {{ $variant?->color?->ColorName }}
                                                        </div>
                                                        <div class="text-dark">
                                                            Size: {{ $variant?->size?->SizeName }}
                                                        </div>
                                                        @if ($product && ! $product->IsActive)
                                                            <div class="text-danger">Sản phẩm đã ngừng kinh doanh</div>
                                                        @elseif ($variant && $cartItem->Quantity > $variant->Stock)
                                                            <div class="text-danger">Chỉ còn {{ $variant->Stock }} sản phẩm trong kho</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="col-2">
                                            <div class="input-group" style="max-width: 120px; margin: auto;">
                                                <!-- NÚT GIẢM -->
                                                <button class="btn btn-outline-secondary btn-sm btn-decrease"
                                                        type="button"
                                                        data-variant-id="{{ $cartItem->VariantID }}">
                                                    <i class="bi bi-dash"></i>
                                                </button>

                                                <!-- INPUT SỐ LƯỢNG -->
                                                <input type="number"
                                                       class="form-control text-center quantity-input"
                                                       value="{{ $cartItem->Quantity }}"
                                                       min="1"
                                                       max="{{ $variant?->Stock ?? 100 }}"
                                                       data-variant-id="{{ $cartItem->VariantID }}"
                                                       data-price="{{ $itemPrice }}"
                                                       readonly>

                                                <!-- NÚT TĂNG -->
                                                <button class="btn btn-outline-secondary btn-sm btn-increase"
                                                        type="button"
                                                        data-variant-id="{{ $cartItem->VariantID }}"
                                                        data-max-stock="{{ $variant?->Stock ?? 0 }}">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Subtotal -->
                                        <div class="col-lg-3 col-md-3 col-4 text-end">
                                            <span class="item-total">
                                                {{ vnd($itemTotal) }}
                                            </span>
                                        </div>

                                        <!-- Remove Button -->
                                        <div class="col-2 text-center">
                                            <form action="{{ route('cart.remove', $cartItem->VariantID) }}" method="post" class="d-inline"
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-link text-danger p-0" title="Xóa">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="col-lg-4 col-md-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h4 class="mb-4">Tổng đơn hàng</h4>

                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Tạm tính:</span>
                                    <span class="fw-bold" id="subtotal">{{ vnd($totalAmount) }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span class="text-muted">Tính khi thanh toán</span>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between mb-4">
                                    <span class="h5 mb-0">Tổng cộng:</span>
                                    <span class="h5 mb-0 text-danger fw-bold" id="grandTotal">
                                        {{ vnd($totalAmount) }}
                                    </span>
                                </div>

                                <!-- Buttons -->
                                <div class="d-grid gap-2">
                                    <a href="{{ route('product.index') }}" class="btn btn-dark btn-rounded-none w-100">
                                        <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
                                    </a>
                                    <a href="{{ route('cart.checkout') }}"
                                       class="btn btn-primary btn-rounded-none w-100">
                                        <i class="fa-solid fa-credit-card me-2"></i>Thanh toán
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            // XỬ LÝ NÚT GIẢM (-)
            $('.btn-decrease').on('click', function () {
                var $btn = $(this);
                var variantId = $btn.data('variant-id');
                var $input = $btn.siblings('.quantity-input');
                var currentQty = parseInt($input.val());

                // VALIDATE: Không cho giảm dưới 1
                if (currentQty <= 1) {
                    alert('Số lượng tối thiểu là 1. Nếu muốn xóa, vui lòng nhấn nút xóa.');
                    return;
                }

                updateQuantity(variantId, currentQty - 1);
            });

            // XỬ LÝ NÚT TĂNG (+)
            $('.btn-increase').on('click', function () {
                var $btn = $(this);
                var variantId = $btn.data('variant-id');
                var maxStock = parseInt($btn.data('max-stock'));
                var $input = $btn.siblings('.quantity-input');
                var currentQty = parseInt($input.val());

                // VALIDATE: Không cho vượt tồn kho
                if (currentQty >= maxStock) {
                    alert('Chỉ còn ' + maxStock + ' sản phẩm trong kho.');
                    return;
                }

                updateQuantity(variantId, currentQty + 1);
            });

            // HÀM GỌI API CẬP NHẬT SỐ LƯỢNG
            function updateQuantity(variantId, newQuantity) {
                var $cartItem = $('.cart-item[data-variant-id="' + variantId + '"]');
                $cartItem.css('opacity', '0.6');

                $.ajax({
                    url: @json(route('cart.update-quantity')),
                    type: 'POST',
                    data: {
                        variantId: variantId,
                        quantity: newQuantity
                    },
                    success: function (response) {
                        $cartItem.css('opacity', '1');

                        if (response.success) {
                            $cartItem.find('.quantity-input').val(newQuantity);
                            $cartItem.find('.item-total').text(response.itemTotal + 'đ');
                            $('#subtotal').text(response.grandTotal + 'đ');
                            $('#grandTotal').text(response.grandTotal + 'đ');

                            // ANIMATION: Flash xanh
                            $cartItem.addClass('bg-success bg-opacity-10');
                            setTimeout(function () {
                                $cartItem.removeClass('bg-success bg-opacity-10');
                            }, 500);
                        } else {
                            alert(response.message || 'Có lỗi xảy ra');
                            location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        $cartItem.css('opacity', '1');
                        console.error('AJAX Error:', error);
                        alert('Có lỗi xảy ra. Vui lòng thử lại.');
                        location.reload();
                    }
                });
            }

        });
    </script>
@endsection
