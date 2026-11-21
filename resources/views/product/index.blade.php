@extends('layouts.app')

@section('title', $categoryName ?? 'Sản phẩm')

@section('content')
    <section class="product-grid py-5">
        <div class="container">
            <div class="row flex-row-reverse g-md-5">
                <main class="col-md-9 col-sm-12">
                    <div class="filter-shop d-flex justify-content-between">
                        @if ($products->isNotEmpty())
                            <div class="showing-product">
                                <p>
                                    Hiển thị {{ $firstIndex }}–{{ $firstIndex + $products->count() - 1 }} trong số {{ $totalProducts }} sản phẩm
                                    @if ($categoryName)
                                        thuộc <strong>{{ $categoryName }}</strong>
                                    @endif
                                </p>
                            </div>

                            <div class="sort-by pb-3">
                                <form action="{{ route('product.index') }}" method="get">
                                    @if ($catId)
                                        <input type="hidden" name="catID" value="{{ $catId }}"/>
                                    @endif
                                    @if ($kw !== '')
                                        <input type="hidden" name="kw" value="{{ $kw }}"/>
                                    @endif
                                    @if ($price)
                                        <input type="hidden" name="price" value="{{ $price }}"/>
                                    @endif
                                    <select name="sort" class="form-control bg-transparent" onchange="this.form.submit()">
                                        @foreach ([0 => 'Mặc định', 1 => 'Tên (A - Z)', 2 => 'Tên (Z - A)', 3 => 'Giá (Thấp - Cao)', 4 => 'Giá (Cao - Thấp)'] as $value => $label)
                                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        @if ($products->isEmpty())
                            <p class="text-center">Không tìm thấy sản phẩm nào</p>
                        @endif
                        @foreach ($products as $product)
                            <article class="col-md-4 mb-5 product-item link-effect">
                                @include('partials.product-card', ['product' => $product])
                            </article>
                        @endforeach
                    </div>

                    @if ($products->isNotEmpty() && $totalPages > 1)
                        <nav aria-label="Page navigation" class="d-flex justify-content-center mt-5">
                            <ul class="pagination">

                                <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                    <a class="page-link"
                                       href="{{ $currentPage > 1 ? route('product.index', $filters + ['page' => $currentPage - 1]) : '#' }}"
                                       aria-label="Previous">
                                        <svg class="arrow-left" width="18" height="20">
                                            <use xlink:href="#arrow-left"></use>
                                        </svg>
                                    </a>
                                </li>
                                @for ($i = 1; $i <= $totalPages; $i++)
                                    <li class="page-item {{ $currentPage === $i ? 'active' : '' }}" @if ($currentPage === $i) aria-current="page" @endif>
                                        <a class="page-link" href="{{ route('product.index', $filters + ['page' => $i]) }}">
                                            {{ $i }}
                                        </a>
                                    </li>
                                @endfor
                                <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                                    <a class="page-link"
                                       href="{{ $currentPage < $totalPages ? route('product.index', $filters + ['page' => $currentPage + 1]) : '#' }}"
                                       aria-label="Next">
                                        <svg class="arrow-right" width="18" height="20">
                                            <use xlink:href="#arrow-right"></use>
                                        </svg>
                                    </a>
                                </li>

                            </ul>
                        </nav>
                    @endif

                </main>

                <aside class="col-md-3 col-sm-12 mt-5">
                    <div class="sidebar">
                        <div class="widget-menu">
                            <div class="widget-search-bar">
                                <form action="{{ route('product.index') }}" method="get">
                                    <input class="form-control search-field ps-3 bg-transparent" id="kw" name="kw"
                                           placeholder="Tìm kiếm..." type="search" value="{{ $kw }}">
                                </form>
                            </div>
                        </div>
                        <div class="widget-product-categories mt-3">
                            @foreach ($categoryGroups as $group)
                                @php $groupOpen = $group->categories->contains('CategoryID', $catId); @endphp
                                <h5 class="widget-title">
                                    <a class="cat-parent-link {{ $groupOpen ? '' : 'collapsed' }} text-decoration-underline"
                                       data-bs-toggle="collapse"
                                       href="#catCollapse_{{ $group->GroupID }}"
                                       role="button"
                                       aria-expanded="{{ $groupOpen ? 'true' : 'false' }}"
                                       aria-controls="catCollapse_{{ $group->GroupID }}">
                                        {{ $group->GroupName }}
                                        <span class="cat-icon">▼</span>
                                    </a>
                                </h5>

                                <ul class="list-unstyled collapse cat-sub-list {{ $groupOpen ? 'show' : '' }}" id="catCollapse_{{ $group->GroupID }}">
                                    @foreach ($group->categories as $sub)
                                        <li class="cat-sub-item">
                                            <a href="{{ route('product.index', ['catId' => $sub->CategoryID]) }}"
                                               @if ((int) $sub->CategoryID === $catId) class="fw-bold" @endif>
                                                {{ $sub->CategoryName }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </div>
                        <div class="widget-product-categories mt-3">
                            <h5 class="widget-title">
                                <a class="cat-parent-link {{ $price ? '' : 'collapsed' }} text-decoration-underline"
                                   data-bs-toggle="collapse"
                                   href="#priceCollapse"
                                   role="button"
                                   aria-expanded="{{ $price ? 'true' : 'false' }}"
                                   aria-controls="priceCollapse">
                                    Lọc theo giá
                                    <span class="cat-icon">▼</span>
                                </a>
                            </h5>
                            @php $priceFilters = array_diff_key($filters, ['price' => true]); @endphp
                            <ul class="list-unstyled collapse cat-sub-list {{ $price ? 'show' : '' }}"
                                id="priceCollapse">
                                @foreach ([0 => 'Tất cả', 1 => '0-300k', 2 => '300k-500k', 3 => '500k-1000k', 4 => '>1000k'] as $value => $label)
                                    <li class="tags-item">
                                        <a href="{{ route('product.index', $priceFilters + ($value ? ['price' => $value] : [])) }}"
                                           @if ($price === $value) class="fw-bold" @endif>{{ $label }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
