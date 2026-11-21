@extends('layouts.admin')

@section('title', 'Danh mục')

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <span>Danh mục</span>
    </div>

    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h1>Quản lý Danh mục</h1>
                <p class="text-muted mt-1">Danh sách tất cả danh mục sản phẩm</p>
            </div>
            <a href="#" onclick="openModal('','addCategoryModal'); return false;" class="btn btn-primary">
                <svg style="width: 16px; height: 16px; margin-right: 0.5rem;" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thêm danh mục
            </a>
        </div>

        <!-- Search -->
        <div class="card mb-6">
            <div class="card-content">
                <form method="get" action="{{ route('admin.category.index') }}" class="d-flex gap-2">
                    <input name="search" type="search" id="searchInput" class="form-control"
                           placeholder="Tìm kiếm danh mục..." value="{{ $search }}">
                    @if ($search !== '')
                        <a href="{{ route('admin.category.index') }}" class="btn btn-outline">Xóa</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-3">
            @forelse ($categories as $category)
                <div class="card">
                    <div class="card-content">
                        <div style="display: flex; align-items: start; gap: 1rem;">
                            <div
                                style="color: white; font-size: 25px; width: 64px; height: 64px; background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-shirt"></i>
                            </div>
                            <div style="flex: 1;">
                                <h3>{{ $category->CategoryName }}</h3>
                                <p class="text-muted mt-1">{{ $category->categoryGroup?->GroupName }} · {{ $category->products_count }} sản phẩm</p>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="button"
                                            onclick="fillEditCategoryModal({{ $category->CategoryID }}, {{ \Illuminate\Support\Js::from($category->CategoryName) }}, {{ (int) $category->GroupID }})"
                                            class="btn btn-outline btn-sm">Sửa
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="confirmDelete({{ $category->CategoryID }})">Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">Không tìm thấy danh mục nào.</p>
            @endforelse
        </div>
    </div>

    {{-- Thêm danh mục --}}
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Thêm danh mục mới</h3>
                <button type="button" class="btn btn-icon" onclick="closeModal('addCategoryModal')"
                        style="background: none; border: none; font-size: 24px;">×
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.category.create') }}" method="post" id="createCategoryForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input name="CategoryName" type="text" class="form-control" required maxlength="100"
                               placeholder="Nhập tên danh mục">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Danh mục cha <span class="text-danger">*</span></label>
                        <select class="form-control" name="GroupID" required>
                            <option value="">Chọn danh mục cha</option>
                            @foreach ($categoryGroups as $group)
                                <option value="{{ $group->GroupID }}">{{ $group->GroupName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal('addCategoryModal')">Hủy</button>
                        <button class="btn btn-primary" type="submit">
                            Thêm danh mục
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sửa danh mục --}}
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Chỉnh sửa danh mục</h3>
                <button type="button" class="btn btn-icon" onclick="closeModal('editCategoryModal')"
                        style="background: none; border: none; font-size: 24px;">×
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.category.update') }}" method="post" id="editCategoryForm">
                    @csrf
                    <input type="hidden" name="CategoryID" value="">
                    <div class="form-group">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input name="CategoryName" type="text" class="form-control" required maxlength="100"
                               placeholder="Nhập tên danh mục">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Danh mục cha <span class="text-danger">*</span></label>
                        <select class="form-control" name="GroupID" required>
                            @foreach ($categoryGroups as $group)
                                <option value="{{ $group->GroupID }}">{{ $group->GroupName }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editCategoryModal')">Hủy</button>
                <button type="button" class="btn btn-primary"
                        onclick="if(validateForm('editCategoryForm')) { closeModal('editCategoryModal'); submitUpdateForm()}">
                    Cập nhật
                </button>
            </div>
        </div>
    </div>
@endsection

@section('forms')
    <form action="{{ route('admin.category.delete') }}" method="post" id="deleteCategoryForm" class="d-none">
        @csrf
        <input type="hidden" name="id" value="">
    </form>
@endsection

@section('scripts')
    <script src="{{ asset('Content/js/Admin/category-admin.js') }}"></script>
@endsection
