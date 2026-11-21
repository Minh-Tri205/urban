<?php

return [
    // Thư mục chứa file sao lưu (.sql). Bản gốc dùng D:\Backups
    'path' => env('BACKUP_PATH', storage_path('app/backups')),

    // Đường dẫn công cụ MySQL (để trống thì dùng lệnh trong PATH)
    'mysqldump' => env('MYSQLDUMP_PATH', 'mysqldump'),
    'mysql' => env('MYSQL_PATH', 'mysql'),

    'connection' => env('DB_CONNECTION', 'mysql'),

    // Giá trị mặc định khi chưa lưu cài đặt trên trang quản trị
    'auto_enabled' => filter_var(env('AUTO_BACKUP_ENABLED', false), FILTER_VALIDATE_BOOL),
    'auto_time' => env('AUTO_BACKUP_TIME', '02:00'),

    // Cài đặt lưu ở trang quản trị (thay cho việc ghi Web.config của bản gốc)
    'settings_file' => storage_path('app/backup-settings.json'),

    'max_upload_kb' => 20480,
    'timeout_seconds' => 600,
];
