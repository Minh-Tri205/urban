{{-- Tương đương @Html.ValidationSummary --}}
@if ($errors->any())
    <div class="{{ $class ?? 'text-danger' }}">
        <ul class="mb-3 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
