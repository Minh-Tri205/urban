{{-- Port từ Views/Product/DanhMuc.cshtml --}}
@foreach ($groups as $item)
    <li class="nav-item dropdown me-4">
        <a class="nav-link dropdown-toggle" href="#" id="dropdownPages" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ $item->GroupName }}
        </a>

        <ul class="dropdown-menu list-unstyled" aria-labelledby="dropdownPages">
            @foreach ($item->categories as $sub)
                <li>
                    <a class="dropdown-item item-anchor" href="{{ route('product.index', ['catId' => $sub->CategoryID]) }}">
                        {{ $sub->CategoryName }}
                    </a>
                </li>
            @endforeach
        </ul>
    </li>
@endforeach
