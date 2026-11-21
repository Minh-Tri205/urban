<?php

use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sao lưu thủ công: php artisan backup:run
Artisan::command('backup:run', function (DatabaseBackupService $backups) {
    $this->info('Đã sao lưu: ' . $backups->backup());
})->purpose('Sao lưu database ra file .sql');

// Sao lưu tự động theo giờ cài trên trang Admin > Sao lưu & Khôi phục
Artisan::command('backup:auto', function (DatabaseBackupService $backups) {
    $path = $backups->runScheduledBackup();
    $this->line($path ? "Đã sao lưu tự động: {$path}" : 'Chưa tới giờ sao lưu hoặc hôm nay đã sao lưu.');
})->purpose('Sao lưu tự động 1 lần/ngày theo cài đặt');

// Khôi phục khi không vào được trang Admin (ví dụ database bị xóa):
//   php artisan backup:restore                    → chọn từ danh sách trong thư mục sao lưu
//   php artisan backup:restore D:\Backups\x.sql   → khôi phục từ file bất kỳ
// Database chưa có sẽ được tạo tự động.
Artisan::command('backup:restore {file? : Tên file trong thư mục sao lưu hoặc đường dẫn đầy đủ tới file .sql} {--force : Không hỏi xác nhận}', function (DatabaseBackupService $backups) {
    $file = $this->argument('file');

    if ($file === null) {
        $names = $backups->listBackups()->pluck('name')->all();
        if ($names === []) {
            $this->error('Không có bản sao lưu nào trong ' . $backups->directory() . '. Hãy truyền đường dẫn file: php artisan backup:restore D:\\duong_dan\\file.sql');

            return 1;
        }
        $file = $this->choice('Chọn bản sao lưu (mới nhất ở đầu)', $names, 0);
    }

    $path = is_file($file) ? $file : $backups->findBackup($file);
    if ($path === null) {
        $this->error("Không tìm thấy file: {$file}");

        return 1;
    }

    $database = $backups->databaseName();
    if (! $this->option('force') && ! $this->confirm("Khôi phục database [{$database}] từ " . basename($path) . '? Dữ liệu hiện tại (nếu có) sẽ bị thay thế.', true)) {
        return 1;
    }

    $safety = $backups->restore($path);

    $this->info("Đã khôi phục database [{$database}] từ " . basename($path));
    if ($safety) {
        $this->line('Dữ liệu trước khi khôi phục được lưu tại: ' . $safety);
    }

    return 0;
})->purpose('Khôi phục database từ file .sql (tự tạo database nếu chưa có)');

Schedule::command('backup:auto')->everyMinute()->withoutOverlapping();
