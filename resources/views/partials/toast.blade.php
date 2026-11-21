{{-- Port từ Views/Shared/_UrbanToast.cshtml: TempData["Success"] / TempData["Error"] thành session flash --}}
@php
    $successMessage = session('Success');
    $errorMessage = session('Error');
@endphp

@if (! empty($successMessage))
    <div id="urban-toast" class="urban-toast urban-toast-success">
        <div>
            <div class="urban-toast__title">Thông báo</div>
            <div>{{ $successMessage }}</div>
        </div>
        <span class="urban-toast__close" onclick="hideUrbanToast()">×</span>
    </div>
@elseif (! empty($errorMessage))
    <div id="urban-toast" class="urban-toast urban-toast-error">
        <div>
            <div class="urban-toast__title">Thông báo lỗi</div>
            <div>{{ $errorMessage }}</div>
        </div>
        <span class="urban-toast__close" onclick="hideUrbanToast()">×</span>
    </div>
@endif

<script>
    function hideUrbanToast() {
        var el = document.getElementById('urban-toast');
        if (el) el.style.display = 'none';
    }

    // Tự ẩn sau 4s
    setTimeout(hideUrbanToast, 4000);
</script>
