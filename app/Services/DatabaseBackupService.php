<?php

namespace App\Services;

use Illuminate\Support\Collection;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Port từ Areas/Admin/Controllers/BackupController.cs.
 * SQL Server: BACKUP DATABASE / RESTORE DATABASE (.bak)  →  MySQL: mysqldump / mysql (.sql).
 * Mật khẩu truyền qua biến môi trường MYSQL_PWD để không lộ trên dòng lệnh.
 */
class DatabaseBackupService
{
    private const FILE_PATTERN = '/^[A-Za-z0-9_\-]+\.sql$/';

    /**
     * Thư mục người dùng đã chọn trên trang quản trị (chưa kiểm tra tồn tại).
     * Chưa chọn thì dùng BACKUP_PATH trong .env.
     */
    public function configuredDirectory(): string
    {
        $chosen = $this->settings()['directory'];

        return is_string($chosen) && trim($chosen) !== '' ? $chosen : (string) config('backup.path');
    }

    /** Thư mục sao lưu, tạo nếu chưa có. */
    public function directory(): string
    {
        return $this->ensureDirectory($this->configuredDirectory());
    }

    /**
     * Đổi thư mục lưu file sao lưu (áp dụng cho cả sao lưu thủ công và tự động).
     *
     * @return string đường dẫn thư mục đã chuẩn hóa
     */
    public function changeDirectory(string $path): string
    {
        $path = trim($path);

        if ($path === '' || mb_strlen($path) > 255) {
            throw new RuntimeException('Vui lòng nhập đường dẫn thư mục (tối đa 255 ký tự).');
        }

        if (preg_match('#^([A-Za-z]:[\\\\/]|[\\\\/])#', $path) !== 1) {
            throw new RuntimeException('Đường dẫn phải là đường dẫn tuyệt đối, ví dụ D:\\Backups.');
        }

        $real = (string) realpath($this->ensureDirectory($path));

        if (! is_writable($real)) {
            throw new RuntimeException("Không có quyền ghi vào thư mục: {$real}");
        }

        // Thư mục public/ ai cũng tải được qua trình duyệt → không cho lưu dữ liệu ở đó
        if ($this->isInside($real, public_path())) {
            throw new RuntimeException('Không được lưu bản sao lưu trong thư mục public của website (ai cũng tải được).');
        }

        $this->saveSettings(['directory' => $real]);

        return $real;
    }

    public function databaseName(): string
    {
        return (string) $this->connectionConfig()['database'];
    }

    /**
     * Sao lưu toàn bộ database ra file .sql, trả về đường dẫn file.
     * $directory = null: lưu vào thư mục sao lưu đã chọn.
     */
    public function backup(string $suffix = '', ?string $directory = null): string
    {
        $fileName = sprintf('%s_%s%s.sql', $this->databaseName(), now()->format('Ymd_His'), $suffix !== '' ? '_' . $suffix : '');
        $directory = $directory === null ? $this->directory() : $this->ensureDirectory($directory);
        $path = $directory . DIRECTORY_SEPARATOR . $fileName;

        $process = $this->process([
            config('backup.mysqldump'),
            ...$this->connectionArguments(),
            '--single-transaction',
            '--routines',
            '--triggers',
            '--add-drop-table',
            '--default-character-set=utf8mb4',
            '--result-file=' . $path,
            $this->databaseName(),
        ]);

        $process->run();

        if (! $process->isSuccessful() || ! is_file($path) || filesize($path) === 0) {
            @unlink($path);
            throw new RuntimeException('Sao lưu thất bại: ' . $this->errorOutput($process));
        }

        return $path;
    }

    /**
     * Khôi phục database từ file .sql. Nếu database chưa có (bị xóa) thì tạo mới trước.
     * Nếu database đang có dữ liệu thì tạo một bản sao lưu an toàn để có thể quay lại.
     *
     * @return string|null đường dẫn bản sao lưu an toàn; null khi database trống nên không cần
     */
    public function restore(string $sqlFile): ?string
    {
        $this->assertLooksLikeMysqlDump($sqlFile);

        $safetyBackup = $this->tableCount() > 0 ? $this->backup('truoc_khoi_phuc') : null;

        $input = fopen($sqlFile, 'rb');
        if ($input === false) {
            throw new RuntimeException('Không đọc được file khôi phục.');
        }

        try {
            $process = $this->process([
                config('backup.mysql'),
                ...$this->connectionArguments(),
                '--default-character-set=utf8mb4',
                $this->databaseName(),
            ]);
            $process->setInput($input);
            $process->run();
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'Phục hồi thất bại: ' . $this->errorOutput($process)
                . ($safetyBackup ? ' Bản sao lưu trước khi khôi phục: ' . basename($safetyBackup) : '')
            );
        }

        return $safetyBackup;
    }

    /**
     * Số bảng trong database; tạo database (rỗng) nếu chưa có.
     * Kết nối không chọn database nên chạy được cả khi database đã bị xóa.
     */
    public function tableCount(): int
    {
        $c = $this->connectionConfig();
        $pdo = new PDO("mysql:host={$c['host']};port={$c['port']}", $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $name = str_replace('`', '``', $this->databaseName());
        $charset = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($c['charset'] ?? 'utf8mb4'));
        $collation = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($c['collation'] ?? 'utf8mb4_unicode_ci'));
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET {$charset} COLLATE {$collation}");

        $count = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
        $count->execute([$this->databaseName()]);

        return (int) $count->fetchColumn();
    }

    /** @return Collection<int, array{name: string, size: int, modified: int}> */
    public function listBackups(): Collection
    {
        $files = glob($this->directory() . DIRECTORY_SEPARATOR . '*.sql') ?: [];

        return collect($files)
            ->map(fn (string $file) => ['name' => basename($file), 'size' => (int) filesize($file), 'modified' => (int) filemtime($file)])
            ->sortByDesc('modified')
            ->values();
    }

    /** Đường dẫn file sao lưu theo tên; null nếu tên không hợp lệ hoặc không tồn tại. */
    public function findBackup(string $fileName): ?string
    {
        if (preg_match(self::FILE_PATTERN, $fileName) !== 1) {
            return null;
        }

        $path = $this->directory() . DIRECTORY_SEPARATOR . $fileName;

        return is_file($path) ? $path : null;
    }

    /**
     * Sao lưu ra file tạm để tải về máy người dùng (không lưu lại trong thư mục sao lưu).
     * Người gọi tự xóa file sau khi gửi.
     */
    public function backupForDownload(): string
    {
        return $this->backup('', storage_path('app/backup-tmp'));
    }

    // ===== Cài đặt sao lưu tự động =====

    /** @return array{enabled: bool, time: string, last_backup_date: ?string, directory: ?string} */
    public function settings(): array
    {
        $defaults = [
            'enabled' => (bool) config('backup.auto_enabled'),
            'time' => (string) config('backup.auto_time'),
            'last_backup_date' => null,
            'directory' => null,
        ];

        $file = config('backup.settings_file');
        $saved = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;

        return is_array($saved) ? array_merge($defaults, array_intersect_key($saved, $defaults)) : $defaults;
    }

    public function saveSettings(array $values): void
    {
        $settings = array_merge($this->settings(), array_intersect_key($values, $this->settings()));
        $file = config('backup.settings_file');

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }

        file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /**
     * Chạy mỗi phút bởi scheduler: sao lưu 1 lần/ngày khi tới giờ đã cài (hoặc đã quá giờ mà hôm nay chưa sao lưu).
     *
     * @return string|null đường dẫn file nếu có sao lưu
     */
    public function runScheduledBackup(): ?string
    {
        $settings = $this->settings();
        $today = now()->toDateString();

        if (! $settings['enabled'] || $settings['last_backup_date'] === $today || now()->format('H:i') < $settings['time']) {
            return null;
        }

        $path = $this->backup('tu_dong');
        $this->saveSettings(['last_backup_date' => $today]);

        return $path;
    }

    private function ensureDirectory(string $path): string
    {
        $path = rtrim($path, '/\\');

        if (! is_dir($path) && ! @mkdir($path, 0775, true) && ! is_dir($path)) {
            throw new RuntimeException("Không tạo được thư mục sao lưu: {$path}");
        }

        return $path;
    }

    private function isInside(string $path, string $parent): bool
    {
        $normalize = function (string $p): string {
            $p = rtrim(str_replace('\\', '/', (string) (realpath($p) ?: $p)), '/') . '/';

            return PHP_OS_FAMILY === 'Windows' ? mb_strtolower($p) : $p;
        };

        return str_starts_with($normalize($path), $normalize($parent));
    }

    private function assertLooksLikeMysqlDump(string $sqlFile): void
    {
        $handle = @fopen($sqlFile, 'rb');
        $head = $handle ? (string) fread($handle, 2048) : '';
        if ($handle) {
            fclose($handle);
        }

        if (! preg_match('/(MySQL dump|MariaDB dump)/i', $head)) {
            throw new RuntimeException('File không phải bản sao lưu MySQL hợp lệ (tạo bởi mysqldump).');
        }
    }

    private function connectionConfig(): array
    {
        return config('database.connections.' . config('backup.connection'));
    }

    /** @return list<string> */
    private function connectionArguments(): array
    {
        $c = $this->connectionConfig();

        return [
            '--host=' . $c['host'],
            '--port=' . $c['port'],
            '--user=' . $c['username'],
        ];
    }

    private function process(array $command): Process
    {
        $env = ['MYSQL_PWD' => (string) $this->connectionConfig()['password']];

        // Trên Windows, thiếu SYSTEMROOT thì mysqldump không mở được kết nối mạng (lỗi 2004 / 10106).
        // Web server (php artisan serve, Apache, IIS) thường không truyền biến này cho tiến trình con.
        if (PHP_OS_FAMILY === 'Windows') {
            $env['SYSTEMROOT'] = getenv('SYSTEMROOT') ?: ($_SERVER['SYSTEMROOT'] ?? $_SERVER['SystemRoot'] ?? 'C:\\Windows');
        }

        $process = new Process($command, null, $env);
        $process->setTimeout((int) config('backup.timeout_seconds'));

        return $process;
    }

    private function errorOutput(Process $process): string
    {
        $message = trim($process->getErrorOutput() ?: $process->getOutput());

        // Bỏ cảnh báo vô hại, giữ lại dòng lỗi thật
        $lines = array_filter(explode("\n", $message), fn ($line) => ! str_contains($line, 'Using a password on the command line'));

        return mb_substr(trim(implode(' ', $lines)), 0, 300) ?: 'không rõ nguyên nhân (mã lỗi ' . $process->getExitCode() . ').';
    }
}
