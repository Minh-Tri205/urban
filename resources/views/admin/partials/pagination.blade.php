{{-- Port từ Areas/Admin/Views/Shared/Partial/_Pagination.cshtml, dùng với LengthAwarePaginator --}}
@if ($paginator->lastPage() > 1)
    <div class="pagination">
        @if ($paginator->currentPage() > 1)
            <a href="{{ $paginator->previousPageUrl() }}">« Trước</a>
        @endif
        @for ($i = 1; $i <= $paginator->lastPage(); $i++)
            @if ($paginator->currentPage() === $i)
                <span class="active">{{ $i }}</span>
            @else
                <a href="{{ $paginator->url($i) }}">{{ $i }}</a>
            @endif
        @endfor
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Sau »</a>
        @endif
    </div>
@endif
