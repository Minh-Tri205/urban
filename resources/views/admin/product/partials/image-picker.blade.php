{{-- Ô chọn ảnh có xem trước. Biến: $name, $inputId, $previewId, $label, $required, $multiple, $current (url ảnh hiện tại), $hint --}}
<div class="form-group">
    <label class="form-label" for="{{ $inputId }}">{!! $label !!}</label>
    <div style="border: 2px dashed var(--border); border-radius: var(--radius); padding: 1rem; text-align: center; cursor: pointer;"
         onclick="document.getElementById('{{ $inputId }}').click()">
        <input type="file" id="{{ $inputId }}" name="{{ $name }}" accept="image/png,image/jpeg,image/webp"
               style="display: none;" @if (! empty($multiple)) multiple @endif @if (! empty($required)) required @endif
               onchange="previewImages(this, '{{ $previewId }}', {{ (int) ($maxFiles ?? 1) }})">
        <div id="{{ $previewId }}">
            @if (! empty($current))
                <img alt="Ảnh hiện tại" src="{{ $current }}" style="width: 100%; border-radius: var(--radius);">
            @else
                <svg style="width: 40px; height: 40px; color: var(--muted-foreground); margin: 0 auto 0.5rem;"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <p class="mb-1">Click để tải ảnh lên</p>
            @endif
            <p class="text-muted mb-0" style="font-size: 12px;">{{ $hint ?? 'PNG, JPG hoặc WEBP (tối đa 5MB)' }}</p>
        </div>
    </div>
</div>
