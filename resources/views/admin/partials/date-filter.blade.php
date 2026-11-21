{{-- Bộ lọc ngày dùng chung cho các trang báo cáo --}}
<form method="get" class="row g-2 mb-4">
    <div class="col-md-3">
        <input type="date" name="fromDate" value="{{ $fromDate }}" class="form-control" aria-label="Từ ngày">
    </div>
    <div class="col-md-3">
        <input type="date" name="toDate" value="{{ $toDate }}" class="form-control" aria-label="Đến ngày">
    </div>
    <div class="col-md-2">
        <button class="btn btn-success w-100">{{ $label ?? 'Lọc' }}</button>
    </div>
</form>
