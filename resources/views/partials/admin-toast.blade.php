{{-- Port từ Areas/Admin/Views/Shared/Partial/_UrbanToast.cshtml --}}
@php
    $successMessage = session('Success');
    $errorMessage = session('Error');
@endphp
<style>
    .urban-toast {
        position: fixed;
        top: 30px;
        right: 20px;
        min-width: 280px;
        max-width: 360px;
        padding: 12px 16px;
        border-radius: 4px;
        color: #111;
        font-size: 14px;
        z-index: 9999;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
    }

    .urban-toast-success {
        background-color: #fff;
        border-left: 4px solid darkgreen;
    }

    .urban-toast-error {
        background-color: #fff;
        border-left: 4px solid var(--bs-primary);
    }

    .urban-toast__title {
        font-weight: 600;
        margin-bottom: 2px;
    }

    .urban-toast__close {
        margin-left: auto;
        cursor: pointer;
        font-weight: bold;
    }
</style>

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
