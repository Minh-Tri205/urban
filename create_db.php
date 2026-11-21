<?php

// Tạo database MySQL theo cấu hình .env (dùng chính bộ đọc cấu hình của Laravel).
// Chạy: php create_db.php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = config('database.connections.mysql');

try {
    $pdo = new PDO("mysql:host={$c['host']};port={$c['port']}", $c['username'], $c['password']);
} catch (PDOException $e) {
    fwrite(STDERR, "Lỗi kết nối MySQL: {$e->getMessage()}\n");
    fwrite(STDERR, "Kiểm tra DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD trong .env và dịch vụ MySQL80 đã chạy chưa.\n");
    exit(1);
}

foreach ([$c['database'], $c['database'] . '_testing'] as $name) {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database {$name} sẵn sàng.\n";
}

echo 'MySQL ' . $pdo->query('SELECT VERSION()')->fetchColumn() . "\n";
