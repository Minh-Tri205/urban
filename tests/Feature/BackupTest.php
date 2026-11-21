<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PDO;
use Tests\TestCase;

/**
 * Sao lưu / khôi phục chạy mysqldump và mysql thật.
 * Phần khôi phục dùng database riêng (fashionstore_restore_test) để không đụng tới transaction của test.
 */
class BackupTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private string $dir;
    private Customer $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = storage_path('framework/testing/backups-' . Str::random(8));
        config([
            'backup.path' => $this->dir,
            'backup.settings_file' => $this->dir . '/settings.json',
        ]);

        $this->admin = Customer::where('UserName', 'admin')->first();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    private function asAdmin(): static
    {
        return $this->withSession(['CustomerID' => $this->admin->CustomerID]);
    }

    private function tools(): void
    {
        if (! is_file((string) config('backup.mysqldump')) || ! is_file((string) config('backup.mysql'))) {
            $this->markTestSkipped('Không tìm thấy mysqldump / mysql theo MYSQLDUMP_PATH, MYSQL_PATH.');
        }
    }

    public function test_backup_page_is_admin_only(): void
    {
        $this->asAdmin()->get('/Admin/Backup/Index')->assertOk()->assertSee('Sao lưu & Khôi phục dữ liệu', false)->assertSee('Chưa có bản sao lưu nào');

        $staff = Customer::where('UserName', 'nguyenvana')->first();
        $staff->update(['RoleID' => 2]);
        $this->flushSession();
        $this->withSession(['CustomerID' => $staff->CustomerID])->get('/Admin/Backup/Index')->assertRedirect(route('admin.dashboard.index'));
        $this->withSession(['CustomerID' => $staff->CustomerID])->post('/Admin/Backup/Action', ['type' => 'now'])->assertRedirect(route('admin.dashboard.index'));
        $this->assertSame([], glob($this->dir . '/*.sql') ?: []);
    }

    public function test_save_settings(): void
    {
        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'save', 'time' => '03:30', 'status' => 'true'])
            ->assertRedirect(route('admin.backup.index'))
            ->assertSessionHas('Success', 'Đã lưu cài đặt!');

        $settings = app(DatabaseBackupService::class)->settings();
        $this->assertTrue($settings['enabled']);
        $this->assertSame('03:30', $settings['time']);

        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'save', 'time' => '25:99', 'status' => 'true'])
            ->assertSessionHas('Error', 'Giờ sao lưu không hợp lệ (định dạng HH:mm).');
        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'hack'])->assertSessionHas('Error', 'Thao tác không hợp lệ.');
    }

    public function test_backup_now_creates_sql_file_and_download(): void
    {
        $this->tools();

        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'now'])->assertSessionHas('Success');

        $files = array_values(array_filter(File::files($this->dir), fn ($f) => $f->getExtension() === 'sql'));
        $this->assertCount(1, $files);
        $content = File::get($files[0]->getPathname());
        $this->assertStringContainsString('MySQL dump', $content);
        $this->assertMatchesRegularExpression('/CREATE TABLE `products`/i', $content);
        $this->assertStringContainsString('Áo Thun Nam Basic', $content);

        $name = $files[0]->getFilename();
        $this->asAdmin()->get('/Admin/Backup/Index')->assertSee($name);
        $this->asAdmin()->get('/Admin/Backup/Download/' . $name)->assertOk()->assertDownload($name);
        $this->asAdmin()->get('/Admin/Backup/Download/..%2F..%2F.env')->assertNotFound();
        $this->asAdmin()->get('/Admin/Backup/Download/khongco.sql')->assertNotFound();
    }

    public function test_change_backup_directory(): void
    {
        $newDir = $this->dir . DIRECTORY_SEPARATOR . 'thu_muc_moi';

        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'directory', 'directory' => $newDir])
            ->assertRedirect(route('admin.backup.index'))
            ->assertSessionHas('Success');

        $service = app(DatabaseBackupService::class);
        $this->assertDirectoryExists($newDir);
        $this->assertSame(realpath($newDir), $service->directory());
        $this->asAdmin()->get('/Admin/Backup/Index')->assertSee(realpath($newDir));

        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'directory', 'directory' => ''])
            ->assertSessionHas('Error', 'Vui lòng nhập đường dẫn thư mục (tối đa 255 ký tự).');
        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'directory', 'directory' => 'backups/tuong_doi'])
            ->assertSessionHas('Error', 'Đường dẫn phải là đường dẫn tuyệt đối, ví dụ D:\\Backups.');
        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'directory', 'directory' => public_path()])
            ->assertSessionHas('Error', 'Không được lưu bản sao lưu trong thư mục public của website (ai cũng tải được).');

        // Lỗi không làm mất thư mục đã chọn trước đó
        $this->assertSame(realpath($newDir), $service->directory());
    }

    public function test_export_downloads_backup_without_keeping_it(): void
    {
        $this->tools();

        $response = $this->asAdmin()->post('/Admin/Backup/Export');
        $response->assertOk();
        $this->assertMatchesRegularExpression('/\.sql/', (string) $response->headers->get('content-disposition'));
        $tempFile = $response->baseResponse->getFile()->getPathname();
        $this->assertStringContainsString('MySQL dump', file_get_contents($tempFile));
        @unlink($tempFile); // khi chạy thật, file tạm bị xóa sau khi gửi (deleteFileAfterSend)

        $this->assertSame([], glob($this->dir . '/*.sql') ?: []);
    }

    public function test_restore_rejects_invalid_files(): void
    {
        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'restore'])
            ->assertSessionHas('Error', 'Vui lòng chọn file sao lưu để phục hồi.');

        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'restore', 'file' => UploadedFile::fake()->create('data.txt', 1)])
            ->assertSessionHas('Error', 'Chỉ chấp nhận file .sql được tạo bởi chức năng sao lưu.');

        $this->asAdmin()->post('/Admin/Backup/Action', [
            'type' => 'restore',
            'file' => UploadedFile::fake()->createWithContent('evil.sql', "DROP DATABASE fashionstore_testing;"),
        ])->assertSessionHas('Error', 'File không phải bản sao lưu MySQL hợp lệ (tạo bởi mysqldump).');

        $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'restore', 'backup' => '../../.env'])
            ->assertSessionHas('Error', 'Vui lòng chọn file sao lưu để phục hồi.');

        $this->assertGreaterThan(0, DB::table('Products')->count());
    }

    public function test_backup_and_restore_round_trip_on_separate_database(): void
    {
        $this->tools();

        $base = config('database.connections.mysql');
        $pdo = new PDO("mysql:host={$base['host']};port={$base['port']}", $base['username'], $base['password']);
        $pdo->exec('DROP DATABASE IF EXISTS fashionstore_restore_test');
        $pdo->exec('CREATE DATABASE fashionstore_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        config([
            'database.connections.restore_test' => array_merge($base, ['database' => 'fashionstore_restore_test']),
            'backup.connection' => 'restore_test',
        ]);

        try {
            $db = DB::connection('restore_test');
            $db->statement('CREATE TABLE Ghi_chu (id INT PRIMARY KEY, noi_dung VARCHAR(100))');
            $db->table('Ghi_chu')->insert([['id' => 1, 'noi_dung' => 'Dữ liệu gốc'], ['id' => 2, 'noi_dung' => 'Tiếng Việt có dấu']]);

            $service = app(DatabaseBackupService::class);
            $backupFile = $service->backup();

            $db->table('Ghi_chu')->delete();
            $db->table('Ghi_chu')->insert(['id' => 3, 'noi_dung' => 'Dữ liệu sai']);

            $this->asAdmin()->post('/Admin/Backup/Action', ['type' => 'restore', 'backup' => basename($backupFile)])
                ->assertSessionHas('Success');

            $this->assertSame(['Dữ liệu gốc', 'Tiếng Việt có dấu'], $db->table('Ghi_chu')->orderBy('id')->pluck('noi_dung')->all());

            // Bản an toàn được tạo trước khi khôi phục chứa dữ liệu "sai" lúc đó
            $safety = collect(File::files($this->dir))->first(fn ($f) => str_contains($f->getFilename(), 'truoc_khoi_phuc'));
            $this->assertNotNull($safety);
            $this->assertStringContainsString('Dữ liệu sai', File::get($safety->getPathname()));
        } finally {
            DB::purge('restore_test');
            $pdo->exec('DROP DATABASE IF EXISTS fashionstore_restore_test');
        }
    }

    public function test_restore_command_recreates_missing_database(): void
    {
        $this->tools();

        $base = config('database.connections.mysql');
        $pdo = new PDO("mysql:host={$base['host']};port={$base['port']}", $base['username'], $base['password']);
        $pdo->exec('DROP DATABASE IF EXISTS fashionstore_restore_test');
        $pdo->exec('CREATE DATABASE fashionstore_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        config([
            'database.connections.restore_test' => array_merge($base, ['database' => 'fashionstore_restore_test']),
            'backup.connection' => 'restore_test',
        ]);

        try {
            DB::connection('restore_test')->statement('CREATE TABLE Ghi_chu (id INT PRIMARY KEY, noi_dung VARCHAR(100))');
            DB::connection('restore_test')->table('Ghi_chu')->insert(['id' => 1, 'noi_dung' => 'Còn nguyên']);
            $backupFile = app(DatabaseBackupService::class)->backup();

            // Mất database
            DB::purge('restore_test');
            $pdo->exec('DROP DATABASE fashionstore_restore_test');

            $this->artisan('backup:restore', ['file' => basename($backupFile), '--force' => true])->assertSuccessful();

            $this->assertSame(['Còn nguyên'], DB::connection('restore_test')->table('Ghi_chu')->pluck('noi_dung')->all());
            // Database trống trước khi khôi phục → không tạo bản an toàn
            $this->assertCount(1, glob($this->dir . '/*.sql'));
        } finally {
            DB::purge('restore_test');
            $pdo->exec('DROP DATABASE IF EXISTS fashionstore_restore_test');
        }
    }

    public function test_scheduled_backup_runs_once_per_day_after_configured_time(): void
    {
        $this->tools();

        $service = app(DatabaseBackupService::class);
        $service->saveSettings(['enabled' => true, 'time' => '02:00']);

        $this->travelTo(now()->setDate(2026, 9, 14)->setTime(1, 59));
        $this->assertNull($service->runScheduledBackup());

        $this->travelTo(now()->setTime(2, 0));
        $this->assertNotNull($service->runScheduledBackup());
        $this->assertSame('2026-09-14', $service->settings()['last_backup_date']);

        $this->travelTo(now()->setTime(23, 0));
        $this->assertNull($service->runScheduledBackup());

        $service->saveSettings(['enabled' => false]);
        $this->travelTo(now()->addDay()->setTime(3, 0));
        $this->assertNull($service->runScheduledBackup());

        $this->assertCount(1, glob($this->dir . '/*.sql'));
        $this->artisan('backup:auto')->assertSuccessful();
    }
}
