@extends('layouts.app')

@section('title', 'Trang chủ')

@section('styles')
    <style>
        body {
            margin: 0;
        }
    </style>
@endsection

@section('content')
    <!-- Section đầu trang với ảnh và liên kết -->
    <section id="banner-header" class="position-relative">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-md-12 p-0">
                    <div class="banner-container position-relative" style="height: calc(100vh - 100px); max-height: 666px">
                        <img src="{{ asset('Content/img/banner-image1.jpg') }}" alt="banner" class="img-fluid w-100" style="max-height: 100%; object-fit: cover;">
                        <div class="banner-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.3);">
                            <div class="text-center text-white">
                                <h1 class="display-3 text-capitalize mb-3">Chào mừng đến với URBAN</h1>
                                <a href="{{ route('product.index') }}" class="btn btn-medium btn-arrow outline-light position-relative">
                                    <span class="text-capitalize">Khám phá ngay</span>
                                    <svg class="arrow-right position-absolute" width="18" height="20">
                                        <use xlink:href="#arrow-right"></use>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Giới thiệu -->
    <section id="about-us" class="padding-large">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-md-6 offset-md-3">
                    <div class="detail">
                        <div class="display-header text-center">
                            <h2 class="display-2 text-capitalize text-dark pb-2">About Us</h2>
                            <p class="pb-3">
                                Đây là dự án cửa hàng thời trang trực tuyến URBAN, nơi chúng tôi mang đến những sản phẩm thời trang đa dạng và phong cách cho khách hàng.
                            </p>
                            <a href="{{ route('home.about') }}" class="btn btn-medium btn-arrow outline-dark position-relative mt-3">
                                <span class="text-capitalize">About Us</span>
                                <svg class="arrow-right position-absolute" width="18" height="20">
                                    <use xlink:href="#arrow-right"></use>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Sản phẩm nổi bật -->
    <section id="featured-products" class="product-store position-relative py-5 ">
        <div class="container">
            <div class="row">
                <div class="display-header pb-3 d-md-flex justify-content-between align-items-center flex-wrap col-md-12">
                    <h2 class="display-2 text-dark text-capitalize">Sản phẩm</h2>
                    <a href="{{ route('product.index') }}" class="btn btn-medium btn-arrow outline-dark position-relative">
                        <span class="text-capitalize">Xem tất cả</span>
                        <svg class="arrow-right position-absolute" width="18" height="20">
                            <use xlink:href="#arrow-right"></use>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="row">
                <div id="featured-swiper" class="product-swiper col-md-12">
                    <div class="swiper">
                        <div class="swiper-wrapper">
                            @foreach ($products as $product)
                                @include('partials.product-card', ['product' => $product])
                            @endforeach
                        </div>
                    </div>
                    <div class="swiper-pagination text-center mt-5"></div>
                </div>

            </div>
        </div>
    </section>

    <!-- Section Dịch vụ công ty -->
    <section id="company-services" class="py-5 border-top border-bottom">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-md-6 pb-3">
                    <div class="icon-box d-flex align-items-center">
                        <div class="icon-box-icon pt-3 pe-3 pb-3 ps-3">
                            <svg class="shipping-fast">
                                <use xlink:href="#shipping-fast"/>
                            </svg>
                        </div>
                        <div class="icon-box-content ps-3">
                            <h3 class="card-title text-capitalize text-dark mb-0">Giao hàng nhanh</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 pb-3">
                    <div class="icon-box d-flex align-items-center">
                        <div class="icon-box-icon pt-3 pe-3 pb-3 ps-3">
                            <svg class="shopping-cart">
                                <use xlink:href="#shopping-cart"/>
                            </svg>
                        </div>
                        <div class="icon-box-content ps-3">
                            <h3 class="card-title text-capitalize text-dark mb-0">Nhận hàng tại cửa hàng</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 pb-3">
                    <div class="icon-box d-flex align-items-center">
                        <div class="icon-box-icon pt-3 pe-3 pb-3 ps-3">
                            <svg class="gift">
                                <use xlink:href="#gift"/>
                            </svg>
                        </div>
                        <div class="icon-box-content ps-3">
                            <h3 class="card-title text-capitalize text-dark mb-0">Đóng gói đặc biệt</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 pb-3">
                    <div class="icon-box d-flex align-items-center">
                        <div class="icon-box-icon pt-3 pe-3 pb-3 ps-3">
                            <svg class="return">
                                <use xlink:href="#return"/>
                            </svg>
                        </div>
                        <div class="icon-box-content ps-3">
                            <h3 class="card-title text-capitalize text-dark mb-0">Chính sách hoàn trả và hoàn tiền</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
