<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Throwable;

/** Chỉ Admin (RoleID 1), xem middleware admin.only trong routes/web.php. */
class BackupController extends Controller
{
    public function __construct(private DatabaseBackupService $backups)
    {
    }

    public function index()
    {
        $error = null;

        try {
            $files = $this->backups->listBackups();
            $directory = $this->backups->directory();
        } catch (Throwable $e) {
            $files = collect();
            $directory = $this->backups->configuredDirectory();
            $error = $e->getMessage();
        }

        return view('admin.backup.index', [
            'settings' => $this->backups->settings(),
            'files' => $files,
            'directory' => $directory,
            'database' => $this->backups->databaseName(),
            'directoryError' => $error,
            'maxUploadMb' => (int) floor(config('backup.max_upload_kb') / 1024),
        ]);
    }

    /** Một action cho các thao tác như bản gốc: type = save | directory | now | restore. */
    public function action(Request $request)
    {
        return match ($request->input('type')) {
            'save' => $this->saveSettings($request),
            'directory' => $this->changeDirectory($request),
            'now' => $this->backupNow(),
            'restore' => $this->restore($request),
            default => redirect()->route('admin.backup.index')->with('Error', 'Thao tác không hợp lệ.'),
        };
    }

    public function download(string $file)
    {
        $path = $this->backups->findBackup($file);
        abort_if($path === null, 404);

        return response()->download($path, $file, ['Content-Type' => 'application/sql']);
    }

    /** Sao lưu và gửi file về trình duyệt để người dùng chọn nơi lưu trên máy tính. */
    public function export(Request $request)
    {
        try {
            $path = $this->backups->backupForDownload();
        } catch (Throwable $e) {
            report($e);

            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], 500)
                : redirect()->route('admin.backup.index')->with('Error', $e->getMessage());
        }

        return response()->download($path, basename($path), ['Content-Type' => 'application/sql'])->deleteFileAfterSend();
    }

    private function changeDirectory(Request $request)
    {
        try {
            $directory = $this->backups->changeDirectory((string) $request->input('directory'));
        } catch (Throwable $e) {
            return redirect()->route('admin.backup.index')->with('Error', $e->getMessage());
        }

        return redirect()->route('admin.backup.index')->with('Success', 'Đã đổi thư mục lưu thành ' . $directory);
    }

    private function saveSettings(Request $request)
    {
        $validator = validator($request->all(), [
            'time' => ['required', 'date_format:H:i'],
            'status' => ['required', 'in:true,false'],
        ], [
            'time.*' => 'Giờ sao lưu không hợp lệ (định dạng HH:mm).',
            'status.*' => 'Trạng thái không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.backup.index')->with('Error', $validator->errors()->first());
        }

        $this->backups->saveSettings([
            'time' => $request->input('time'),
            'enabled' => $request->input('status') === 'true',
        ]);

        return redirect()->route('admin.backup.index')->with('Success', 'Đã lưu cài đặt!');
    }

    private function backupNow()
    {
        try {
            $path = $this->backups->backup();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('admin.backup.index')->with('Error', $e->getMessage());
        }

        return redirect()->route('admin.backup.index')->with('Success', 'Đã sao lưu vào ' . $path);
    }

    private function restore(Request $request)
    {
        $source = null;

        if ($request->hasFile('file')) {
            $validator = validator($request->all(), [
                'file' => ['file', 'max:' . config('backup.max_upload_kb'), function ($attribute, $value, $fail) {
                    if (strtolower($value->getClientOriginalExtension()) !== 'sql') {
                        $fail('Chỉ chấp nhận file .sql được tạo bởi chức năng sao lưu.');
                    }
                }],
            ], [
                'file.max' => 'File khôi phục không được vượt quá ' . floor(config('backup.max_upload_kb') / 1024) . 'MB.',
                'file.uploaded' => 'Tải file lên thất bại (có thể do file quá lớn).',
            ]);

            if ($validator->fails()) {
                return redirect()->route('admin.backup.index')->with('Error', $validator->errors()->first());
            }

            $source = $request->file('file')->getRealPath();
        } elseif (is_string($request->input('backup')) && $request->input('backup') !== '') {
            $source = $this->backups->findBackup($request->input('backup'));
        }

        if ($source === null) {
            return redirect()->route('admin.backup.index')->with('Error', 'Vui lòng chọn file sao lưu để phục hồi.');
        }

        try {
            $safety = $this->backups->restore($source);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('admin.backup.index')->with('Error', $e->getMessage());
        }

        return redirect()->route('admin.backup.index')->with('Success', $safety
            ? 'Phục hồi thành công! Dữ liệu trước khi phục hồi đã được lưu tại ' . basename($safety)
            : 'Phục hồi thành công!');
    }
}
