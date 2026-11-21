<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model gốc cho các bảng chuyển từ SQL Server: giữ nguyên tên bảng/cột PascalCase,
 * khóa chính dạng {Tên}ID và không dùng created_at/updated_at của Laravel.
 */
abstract class LegacyModel extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}
