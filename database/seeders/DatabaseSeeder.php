<?php

namespace Database\Seeders;

use App\Helpers\PasswordHasher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Mật khẩu mặc định cho toàn bộ tài khoản mẫu.
     * Hash trong file SQL gốc là chuỗi hex giả (không phải base64 hợp lệ)
     * nên được hash lại bằng PasswordHasher để đăng nhập được.
     */
    private const DEFAULT_PASSWORD = '123456';

    public function run(): void
    {
        DB::table('Roles')->insert([
            ['RoleID' => 1, 'RoleName' => 'Admin'],
            ['RoleID' => 2, 'RoleName' => 'Nhân viên'],
            ['RoleID' => 3, 'RoleName' => 'Khách hàng'],
        ]);

        // Dữ liệu mẫu chuyển từ FashionStore.sql
        DB::unprepared(file_get_contents(database_path('seeders/sql/fashionstore_data.sql')));

        DB::table('Customers')->update(['RoleID' => 3]);
        DB::table('Customers')->orderBy('CustomerID')->pluck('CustomerID')->each(function ($id) {
            DB::table('Customers')->where('CustomerID', $id)
                ->update(['PasswordHash' => PasswordHasher::hashPassword(self::DEFAULT_PASSWORD)]);
        });

        $adminId = DB::table('Customers')->insertGetId([
            'UserName' => 'admin',
            'PasswordHash' => PasswordHasher::hashPassword(self::DEFAULT_PASSWORD),
            'Email' => 'admin@fashionstore.vn',
            'IsActive' => true,
            'RoleID' => 1,
        ]);
        DB::table('CustomerProfile')->insert(['ProfileID' => $adminId, 'FullName' => 'Quản trị viên']);

        // Các lệnh UPDATE cuối file SQL gốc, viết lại theo cú pháp MySQL.
        // Bỏ lệnh gán mọi ảnh về 'post-item2.jpg': mỗi sản phẩm đã có ảnh riêng product_001..060.jpg
        DB::statement('
            UPDATE Orders SET TotalAmount = IFNULL((
                SELECT SUM(d.Quantity * d.Price * (1 - d.DiscountPercent / 100.0))
                FROM OrderDetails d
                WHERE d.OrderID = Orders.OrderID
            ), 0)
        ');

        // Quy định mã SKU: P{4 số mã sản phẩm}-{2 số mã màu}{2 số mã size}
        DB::statement("
            UPDATE ProductVariants
            SET SKU = CONCAT('P', LPAD(ProductID, 4, '0'), '-', LPAD(ColorID, 2, '0'), LPAD(SizeID, 2, '0'))
        ");
    }
}
