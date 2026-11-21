@extends('layouts.admin')

@section('title', 'Sao lưu & Khôi phục')

@section('styles')
    <style>
        /* Dùng lại biến màu của dashboard.css để giữ chung phong cách trang quản trị */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-left: 4px solid var(--primary);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
        }

        .stat-card .stat-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--muted-foreground);
        }

        .stat-card .stat-value {
            font-size: 22px;
            font-weight: 600;
            margin-top: .35rem;
        }

        .stat-card .stat-hint {
            font-size: 12px;
            color: var(--muted-foreground);
        }

        .stat-card.is-success { border-left-color: var(--success); }
        .stat-card.is-warning { border-left-color: var(--warning); }
        .stat-card.is-info { border-left-color: var(--info); }

        .backup-columns {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            align-items: start;
        }

        .danger-card { border-top: 3px solid var(--destructive); }

        .note-box {
            background: var(--muted);
            border-radius: var(--radius);
            padding: .75rem .9rem;
            font-size: 12px;
            color: var(--muted-foreground);
        }

        .note-box.is-danger {
            background: #fdf1f2;
            color: #842029;
        }

        .file-name {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-weight: 500;
        }

        .file-icon {
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            border-radius: var(--radius);
            background: var(--accent);
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 1100px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .backup-columns { grid-template-columns: 1fr; }
        }
    </style>
@endsection

@section('content')
    @php
        $latest = $files->first();
        $totalSize = $files->sum('size');
        $kind = function (string $name) {
            return match (true) {
                str_contains($name, '_truoc_khoi_phuc') => ['badge-warning', 'Trước khi khôi phục'],
                str_contains($name, '_tu_dong') => ['badge-info', 'Tự động'],
                default => ['badge-secondary', 'Thủ công'],
            };
        };
    @endphp

    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <span>Sao lưu & Khôi phục</span>
    </div>

    <div class="content-area">
        <div class="mb-6">
            <h1>Sao lưu & Khôi phục dữ liệu</h1>
            <p class="text-muted mt-1">
                Database <strong>{{ $database }}</strong> · Thư mục lưu <code>{{ $directory }}</code>
            </p>
        </div>

        @if ($directoryError)
            <div class="note-box is-danger mb-6">{{ $directoryError }}</div>
        @endif

        <!-- Thống kê nhanh -->
        <div class="stat-grid mb-6">
            <div class="stat-card">
                <div class="stat-label">Số bản sao lưu</div>
                <div class="stat-value">{{ $files->count() }}</div>
            </div>
            <div class="stat-card is-info">
                <div class="stat-label">Tổng dung lượng</div>
                <div class="stat-value">{{ \Illuminate\Support\Number::fileSize($totalSize, 1) }}</div>
            </div>
            <div class="stat-card is-success">
                <div class="stat-label">Bản gần nhất</div>
                <div class="stat-value">
                    {{ $latest ? \Carbon\Carbon::createFromTimestamp($latest['modified'])->timezone(config('app.timezone'))->format('H:i') : '—' }}
                </div>
                <div class="stat-hint">
                    {{ $latest ? \Carbon\Carbon::createFromTimestamp($latest['modified'])->timezone(config('app.timezone'))->format('d/m/Y') : 'Chưa sao lưu lần nào' }}
                </div>
            </div>
            <div class="stat-card is-warning">
                <div class="stat-label">Sao lưu tự động</div>
                <div class="stat-value">
                    <span class="badge {{ $settings['enabled'] ? 'badge-success' : 'badge-secondary' }}">
                        {{ $settings['enabled'] ? 'Đang bật' : 'Đang tắt' }}
                    </span>
                </div>
                <div class="stat-hint">Hằng ngày lúc {{ $settings['time'] }}</div>
            </div>
        </div>

        <div class="backup-columns mb-6">
            <!-- Cột trái: sao lưu -->
            <div class="d-flex flex-column gap-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sao lưu ngay</h3>
                    </div>
                    <div class="card-content">
                        <p class="text-muted mb-3">
                            Tạo một file <code>.sql</code> chứa toàn bộ cấu trúc bảng và dữ liệu hiện tại. Chọn nơi lưu:
                            lưu vào thư mục sao lưu bên dưới, hoặc tải về máy tính và tự chọn nơi lưu.
                        </p>

                        <form method="post" action="{{ route('admin.backup.action') }}" class="mb-3">
                            @csrf
                            <input type="hidden" name="type" value="directory">
                            <label class="form-label" for="backupDirectory">Thư mục lưu</label>
                            <div class="d-flex gap-2">
                                <input type="text" id="backupDirectory" name="directory" class="form-control" required
                                       maxlength="255" placeholder="Ví dụ: D:\Backups" value="{{ $directory }}">
                                <button type="submit" class="btn btn-outline" style="white-space: nowrap;">Đổi thư mục</button>
                            </div>
                            <p class="text-muted mt-1 mb-0" style="font-size: 12px;">
                                Đường dẫn tuyệt đối trên máy chạy website; thư mục chưa có sẽ được tạo. Áp dụng cho cả sao lưu
                                thủ công và tự động, danh sách bên dưới hiển thị file trong thư mục này.
                            </p>
                        </form>

                        <div class="d-flex gap-2">
                            <form method="post" action="{{ route('admin.backup.action') }}"
                                  onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Đang sao lưu...';">
                                @csrf
                                <input type="hidden" name="type" value="now">
                                <button type="submit" class="btn btn-success">Sao lưu vào thư mục</button>
                            </form>
                            <form method="post" action="{{ route('admin.backup.export') }}" id="exportForm">
                                @csrf
                                <button type="submit" class="btn btn-primary">Tải về máy tính…</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Lịch sao lưu tự động</h3>
                    </div>
                    <div class="card-content">
                        <form method="post" action="{{ route('admin.backup.action') }}">
                            @csrf
                            <input type="hidden" name="type" value="save">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="form-group mb-0">
                                    <label class="form-label" for="backupTime">Giờ sao lưu hằng ngày</label>
                                    <input type="time" id="backupTime" name="time" class="form-control" required value="{{ $settings['time'] }}">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label" for="backupStatus">Trạng thái</label>
                                    <select id="backupStatus" name="status" class="form-control">
                                        <option value="true" @selected($settings['enabled'])>Bật</option>
                                        <option value="false" @selected(! $settings['enabled'])>Tắt</option>
                                    </select>
                                </div>
                            </div>

                            <div class="note-box mt-3">
                                Lần sao lưu tự động gần nhất:
                                <strong>{{ $settings['last_backup_date'] ? \Carbon\Carbon::parse($settings['last_backup_date'])->format('d/m/Y') : 'chưa có' }}</strong>.
                                Lịch chỉ chạy khi có tiến trình nền: mở terminal và chạy <code>php artisan schedule:work</code>,
                                hoặc tạo tác vụ Windows Task Scheduler gọi <code>php artisan schedule:run</code> mỗi phút.
                            </div>

                            <button type="submit" class="btn btn-primary mt-3">Lưu cài đặt</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cột phải: khôi phục -->
            <div class="card danger-card">
                <div class="card-header">
                    <h3 class="card-title text-danger">Khôi phục từ file</h3>
                </div>
                <div class="card-content">
                    <form method="post" action="{{ route('admin.backup.action') }}" enctype="multipart/form-data"
                          onsubmit="return confirmRestore(this, 'file đã chọn');">
                        @csrf
                        <input type="hidden" name="type" value="restore">
                        <div class="form-group">
                            <label class="form-label" for="restoreFile">Chọn file sao lưu</label>
                            <input type="file" id="restoreFile" name="file" accept=".sql" class="form-control" required>
                            <p class="text-muted mt-1 mb-0" style="font-size: 12px;">Định dạng .sql, tối đa {{ $maxUploadMb }}MB</p>
                        </div>

                        <div class="note-box is-danger">
                            Toàn bộ dữ liệu hiện tại sẽ bị thay thế. Hệ thống tự sao lưu dữ liệu hiện tại trước khi
                            khôi phục, nên vẫn quay lại được nếu cần.
                        </div>

                        <button type="submit" class="btn btn-danger mt-3 w-100">Khôi phục dữ liệu</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Danh sách bản sao lưu -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Các bản sao lưu ({{ $files->count() }})</h3>
            </div>
            <div class="card-content" style="padding: 0;">
                <div class="table-container">
                    <table class="table m-0">
                        <thead>
                        <tr>
                            <th>Tên file</th>
                            <th style="width: 150px;">Loại</th>
                            <th style="width: 180px;">Thời gian</th>
                            <th class="text-end" style="width: 120px;">Dung lượng</th>
                            <th style="width: 220px;">Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($files as $file)
                            @php
                                [$badgeClass, $badgeText] = $kind($file['name']);
                                $time = \Carbon\Carbon::createFromTimestamp($file['modified'])->timezone(config('app.timezone'));
                            @endphp
                            <tr>
                                <td>
                                    <div class="file-name">
                                        <span class="file-icon">
                                            <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                            </svg>
                                        </span>
                                        <code>{{ $file['name'] }}</code>
                                    </div>
                                </td>
                                <td><span class="badge {{ $badgeClass }}">{{ $badgeText }}</span></td>
                                <td>
                                    {{ $time->format('d/m/Y H:i:s') }}
                                    <p class="text-muted mb-0" style="font-size: 12px;">{{ $time->diffForHumans() }}</p>
                                </td>
                                <td class="text-end">{{ \Illuminate\Support\Number::fileSize($file['size'], 1) }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-outline btn-sm" href="{{ route('admin.backup.download', $file['name']) }}">Tải về</a>
                                        <form method="post" action="{{ route('admin.backup.action') }}"
                                              onsubmit="return confirmRestore(this, {{ \Illuminate\Support\Js::from($file['name']) }});">
                                            @csrf
                                            <input type="hidden" name="type" value="restore">
                                            <input type="hidden" name="backup" value="{{ $file['name'] }}">
                                            <button type="submit" class="btn btn-danger btn-sm">Khôi phục</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Chưa có bản sao lưu nào</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Tải bản sao lưu về máy: Chrome/Edge mở hộp thoại "Lưu thành" để chọn thư mục và tên file.
        // Trình duyệt khác tải về như bình thường (bật "Hỏi nơi lưu mỗi lần tải" để được chọn thư mục).
        document.getElementById('exportForm').addEventListener('submit', async function (e) {
            if (!window.showSaveFilePicker) {
                return;
            }
            e.preventDefault();

            const form = this;
            const button = form.querySelector('button');
            const pad = n => String(n).padStart(2, '0');
            const d = new Date();
            const suggestedName = {{ \Illuminate\Support\Js::from($database) }} + '_' + d.getFullYear() + pad(d.getMonth() + 1) + pad(d.getDate())
                + '_' + pad(d.getHours()) + pad(d.getMinutes()) + pad(d.getSeconds()) + '.sql';

            let handle;
            try {
                handle = await window.showSaveFilePicker({
                    suggestedName,
                    types: [{ description: 'File sao lưu MySQL', accept: { 'application/sql': ['.sql'] } }],
                });
            } catch (err) {
                if (err.name !== 'AbortError') {
                    form.submit(); // không mở được hộp thoại → tải về kiểu thường
                }
                return;
            }

            button.disabled = true;
            button.textContent = 'Đang sao lưu...';
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name=_token]').value },
                });
                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    throw new Error(data.message || 'Sao lưu thất bại (mã lỗi ' + res.status + ').');
                }
                await res.body.pipeTo(await handle.createWritable());
                alert('Đã lưu bản sao lưu: ' + handle.name);
            } catch (err) {
                if (handle.remove) {
                    handle.remove().catch(() => {});
                }
                alert(err.message);
            } finally {
                button.disabled = false;
                button.textContent = 'Tải về máy tính…';
            }
        });

        function confirmRestore(form, name) {
            if (!confirm('Khôi phục dữ liệu từ ' + name + '?\nToàn bộ dữ liệu hiện tại sẽ bị thay thế.')) {
                return false;
            }
            form.querySelectorAll('button').forEach(b => b.disabled = true);
            return true;
        }
    </script>
@endsection
