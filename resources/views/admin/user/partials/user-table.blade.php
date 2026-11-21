{{-- Port từ Areas/Admin/Views/User/getAllUser.cshtml --}}
@php
    $badgeColors = ['badge-warning', 'badge-danger', 'badge-info', 'badge-success', 'badge-primary', 'badge-secondary'];
    $roleBadge = fn ($roleId) => $roleId ? $badgeColors[($roleId - 1) % count($badgeColors)] : 'badge-secondary';
    $currentId = \App\Helpers\CustomerSession::id();
@endphp

<div class="card">
    <div class="card-content" style="padding: 0;">
        <div class="table-container">
            <table class="table" id="dataTable">
                <thead>
                <tr>
                    <th>Người dùng</th>
                    <th>Số điện thoại</th>
                    <th style="width: 220px;">Vai trò</th>
                    <th>Ngày tạo</th>
                    <th style="width: 180px;">Trạng thái</th>
                </tr>
                </thead>

                <tbody>
                @forelse ($users as $user)
                    @php $isSelf = $user->CustomerID === $currentId; @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 500;">
                                    {{ mb_strtoupper(mb_substr($user->FullName ?: $user->UserName, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $user->FullName ?: 'N/A' }}</strong>
                                    <p class="text-muted mb-0" style="font-size: 12px;">{{ $user->UserName }} · {{ $user->Email }}</p>
                                </div>
                            </div>
                        </td>

                        <td>{{ $user->PhoneNumber ?: 'N/A' }}</td>

                        <td>
                            @if ($isSelf)
                                <span class="badge {{ $roleBadge($user->RoleID) }}">{{ $user->RoleName }}</span>
                                <p class="text-muted mb-0" style="font-size: 11px;">Không thể tự đổi vai trò</p>
                            @else
                                {{-- Đổi vai trò: chọn rồi bấm Lưu --}}
                                <form action="{{ route('admin.user.change-role') }}" method="post" class="d-flex gap-1">
                                    @csrf
                                    <input type="hidden" name="CustomerID" value="{{ $user->CustomerID }}">
                                    <select name="RoleID" class="form-control" style="padding: 0.25rem; font-size: 12px;"
                                            aria-label="Vai trò của {{ $user->UserName }}">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->RoleID }}" @selected($role->RoleID === $user->RoleID)>{{ $role->RoleName }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-outline btn-sm">Lưu</button>
                                </form>
                            @endif
                        </td>

                        <td>{{ $user->CreatedAt?->format('d/m/Y') }}</td>

                        <td>
                            @if ($isSelf)
                                <span class="badge badge-success">Hoạt động (bạn)</span>
                            @else
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $user->IsActive ? 'badge-success' : 'badge-danger' }}">
                                        {{ $user->IsActive ? 'Hoạt động' : 'Khoá' }}
                                    </span>
                                    {{-- Khoá / mở khoá tài khoản --}}
                                    <form action="{{ route('admin.user.update-state') }}" method="post"
                                          onsubmit="return confirm({{ \Illuminate\Support\Js::from(($user->IsActive ? 'Khoá' : 'Mở khoá') . ' tài khoản ' . $user->UserName . '?') }});">
                                        @csrf
                                        <input type="hidden" name="CustomerID" value="{{ $user->CustomerID }}">
                                        <button type="submit" class="btn btn-{{ $user->IsActive ? 'danger' : 'success' }} btn-sm">{{ $user->IsActive ? 'Khoá' : 'Mở khoá' }}</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Không tìm thấy người dùng nào</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
