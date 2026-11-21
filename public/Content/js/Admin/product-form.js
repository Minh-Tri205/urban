// Dùng cho trang Thêm / Sửa / Quản lý ảnh sản phẩm (thay cho phần form trong product-admin.js của bản gốc)

const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

function escapeAttr(value) {
    return String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

// Xem trước ảnh, kiểm tra số lượng và dung lượng trước khi gửi
function previewImages(input, previewId, maxFiles) {
    const preview = document.getElementById(previewId);
    const files = Array.from(input.files || []);

    if (maxFiles > 0 && files.length > maxFiles) {
        alert(`Chỉ được chọn tối đa ${maxFiles} ảnh. Bạn đã chọn ${files.length} ảnh.`);
        input.value = '';
        return;
    }

    const tooLarge = files.find(f => f.size > MAX_IMAGE_SIZE);
    if (tooLarge) {
        alert(`Ảnh "${tooLarge.name}" vượt quá 5MB. Vui lòng chọn ảnh khác.`);
        input.value = '';
        return;
    }

    if (files.length === 0) return;

    preview.innerHTML = '';
    const grid = document.createElement('div');
    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = files.length > 1 ? '1fr 1fr' : '1fr';
    grid.style.gap = '0.5rem';
    preview.appendChild(grid);

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = file.name;
            img.style.width = '100%';
            img.style.height = files.length > 1 ? '100px' : 'auto';
            img.style.objectFit = 'cover';
            img.style.borderRadius = 'var(--radius)';
            grid.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

// ===== Tạo biến thể (trang Thêm sản phẩm): Màu × Size =====
let variants = [];

function updateVariants() {
    const colors = Array.from(document.querySelectorAll('input[name="colors"]:checked')).map(cb => ({
        id: cb.dataset.id, name: cb.value, hex: cb.dataset.hex
    }));
    const sizes = Array.from(document.querySelectorAll('input[name="sizes"]:checked')).map(cb => ({
        id: cb.dataset.id, name: cb.value
    }));

    // Giữ lại SKU / tồn kho đã nhập khi thêm bớt màu hoặc size
    const previous = new Map(variants.map(v => [v.color.id + '-' + v.size.id, v]));
    variants = [];
    colors.forEach(color => sizes.forEach(size => {
        const old = previous.get(color.id + '-' + size.id);
        variants.push(old || { color, size, sku: '', stock: 0, status: 'Available' });
    }));

    renderVariantsTable();
}

function renderVariantsTable() {
    const container = document.getElementById('variantsContainer');

    if (variants.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:2rem; color:var(--muted-foreground);"><p>Chọn màu sắc và kích thước để tạo biến thể</p></div>';
        return;
    }

    const rows = variants.map((v, i) => `
        <tr>
          <td>
            <span class="swatch" title="${escapeAttr(v.color.name)}" style="background:${escapeAttr(v.color.hex)}"></span>
            <input name="Variants[${i}][ColorID]" value="${escapeAttr(v.color.id)}" type="hidden">
          </td>
          <td><strong>${escapeAttr(v.size.name)}</strong><input name="Variants[${i}][SizeID]" value="${escapeAttr(v.size.id)}" type="hidden"></td>
          <td><input name="Variants[${i}][SKU]" type="text" class="form-control" value="${escapeAttr(v.sku)}" maxlength="50"
                     oninput="variants[${i}].sku = this.value" placeholder="Auto" style="max-width: 150px"></td>
          <td><input name="Variants[${i}][Stock]" type="number" class="form-control" value="${escapeAttr(v.stock)}" min="0" required
                     oninput="variants[${i}].stock = this.value" style="width:100px;"></td>
          <td>
            <select class="form-control" name="Variants[${i}][Status]" onchange="variants[${i}].status = this.value">
              <option value="Available" ${v.status === 'Available' ? 'selected' : ''}>Còn hàng</option>
              <option value="OutOfStock" ${v.status === 'OutOfStock' ? 'selected' : ''}>Hết hàng</option>
            </select>
          </td>
          <td>
            <button type="button" class="btn btn-danger" onclick="removeVariantRow(${i})" title="Xóa biến thể">
              <svg style="width:16px; height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
            </button>
          </td>
        </tr>`).join('');

    container.innerHTML = `
      <div style="overflow-x:auto;">
        <table class="table">
          <thead><tr><th>Màu sắc</th><th>Size</th><th>SKU</th><th>Tồn kho</th><th>Trạng thái</th><th></th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
      <div style="margin-top:1rem; padding:1rem; background:var(--muted); border-radius:var(--radius);">
        <p style="font-size:12px; color:var(--muted-foreground); margin:0;">
          <strong>Tổng: ${variants.length} biến thể</strong><br>SKU sẽ tự động tạo nếu để trống.
        </p>
      </div>`;
}

function removeVariantRow(index) {
    variants.splice(index, 1);
    renderVariantsTable();
}

// Chặn gửi form 2 lần và yêu cầu có biến thể khi thêm sản phẩm
function setupProductForm(formId, requireVariants) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', e => {
        if (requireVariants && variants.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn màu sắc và kích thước để tạo ít nhất một biến thể.');
            return;
        }
        if (form.dataset.submitted) {
            e.preventDefault();
            return;
        }
        form.dataset.submitted = '1';
        form.querySelectorAll('button[type="submit"]').forEach(b => b.disabled = true);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.size-pill input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => cb.closest('.size-pill').classList.toggle('active', cb.checked));
    });
});
