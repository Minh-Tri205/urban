@extends('layouts.admin')

@section('title', 'Người dùng')

@section('content')
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <span>Người dùng</span>
    </div>

    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-6">
            <div>
                <h1>Quản lý Người dùng</h1>
                <p class="text-muted mt-1">Danh sách tất cả người dùng trong hệ thống</p>
            </div>

            <div class="d-flex flex-wrap gap-3">
                <form method="post" action="{{ route('admin.user.add-role') }}"
                      class="d-flex align-items-center gap-2 justify-content-center">
                    @csrf
                    <input type="text" name="roleName" class="form-control" placeholder="Nhập tên vai trò"
                           style="max-width: 280px;" required maxlength="50"/>

                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Thêm</span>
                    </button>
                </form>

                <form method="post" action="{{ route('admin.user.drop-role') }}"
                      class="d-flex align-items-center gap-2 justify-content-center"
                      onsubmit="return confirm('Bạn có chắc muốn xoá vai trò này?');">
                    @csrf
                    <select name="roleId" class="form-control" required>
                        <option value="">Chọn vai trò</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->RoleID }}">{{ $role->RoleName }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-danger d-flex align-items-center gap-1">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Xoá</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">Tìm kiếm & Lọc</h3>
            </div>
            <div class="card-content">
                {{-- Gửi form thường vẫn lọc được; có JavaScript thì lọc bằng AJAX cho nhanh --}}
                <form id="filterForm" method="get" action="{{ route('admin.user.index') }}">
                    <div class="grid grid-cols-3 gap-3">
                        <input type="text" name="keyword" class="form-control"
                               placeholder="Tìm theo username, họ tên hoặc email" value="{{ $keyword }}"/>

                        <select name="roleId" class="form-control">
                            <option value="">Tất cả vai trò</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->RoleID }}" @selected((string) request('roleId') === (string) $role->RoleID)>{{ $role->RoleName }}</option>
                            @endforeach
                        </select>

                        <select name="isActive" class="form-control">
                            <option value="">Tất cả trạng thái</option>
                            <option value="true" @selected(request('isActive') === 'true')>Hoạt động</option>
                            <option value="false" @selected(request('isActive') === 'false')>Khoá</option>
                        </select>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-outline btn-sm">Xóa bộ lọc</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div id="userTable">
            @include('admin.user.partials.user-table')
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const userTableUrl = @json(route('admin.user.get-all-user'));

        $('#filterForm').on('submit', function (e) {
            e.preventDefault();
            $.get(userTableUrl, $(this).serialize(), function (res) {
                $('#userTable').html(res);
            });
        });

    </script>
@endsection
