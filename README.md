# FashionStore (Laravel 13 + MySQL)

Website bán quần áo thời trang, gồm trang cửa hàng cho khách và trang quản trị cho nhân viên.

## Yêu cầu

| Phần mềm | Phiên bản | Ghi chú |
|---|---|---|
| PHP | 8.3 trở lên | Cần bật các extension: `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo`, `openssl` |
| Composer | 2.x | |
| MySQL | 8.0 trở lên | |

Không cần Node.js hay npm: toàn bộ CSS/JS là file tĩnh có sẵn trong `public/`.

Kiểm tra máy đã sẵn sàng chưa:

```bash
php -v
php -m
composer -V
mysql --version
```

Nếu báo `'php' is not recognized`, xem mục [Xử lý sự cố](#xử-lý-sự-cố).

## Cài đặt

```bash
# 1. Cài thư viện PHP
composer install

# 2. Tạo file cấu hình
copy .env.example .env        # Linux/macOS: cp .env.example .env
php artisan key:generate
```

Mở `.env` và sửa thông tin MySQL cho đúng máy của bạn:

```ini
DB_DATABASE=fashionstore
DB_USERNAME=root
DB_PASSWORD=mật_khẩu_mysql_của_bạn
```

```bash
# 3. Tạo database (đọc cấu hình từ .env)
php create_db.php

# 4. Tạo bảng và nạp dữ liệu mẫu
php artisan migrate --seed

# 5. Chạy web
php artisan serve
```

Mở `http://127.0.0.1:8000`.

Ba bước 1, 3, 4 có thể gộp lại bằng `composer setup`.

## Tài khoản mẫu

| Vai trò | Tài khoản | Mật khẩu |
|---|---|---|
| Quản trị viên | `admin` | `123456` |
| Khách hàng | `nguyenvana`, `tranthib`, ... | `123456` |

Dữ liệu mẫu gồm 51 tài khoản, 20 sản phẩm, 250 biến thể, 60 ảnh và 50 đơn hàng.

> Ngày đặt của đơn hàng mẫu nằm trong **tháng 11/2024**. Vào Dashboard, hãy chọn khoảng ngày bao gồm tháng
> 11/2024 (mặc định chỉ lấy 7 ngày gần nhất) thì biểu đồ mới có dữ liệu.

Nạp lại dữ liệu mẫu từ đầu (xóa sạch dữ liệu hiện có):

```bash
php artisan migrate:fresh --seed
```

## Phân quyền

| Vai trò | RoleID | Quyền |
|---|---|---|
| Quản trị viên | 1 | Toàn bộ trang quản trị |
| Nhân viên | 2 | Dashboard, Sản phẩm, Danh mục, Đơn hàng |
| Khách hàng | 3 | Trang cửa hàng |

Người dùng và Sao lưu/Khôi phục chỉ dành cho quản trị viên.

## Cấu hình thêm

### Gửi email (chức năng Quên mật khẩu)

Điền `MAIL_USERNAME` và `MAIL_PASSWORD` trong `.env`. Với Gmail phải dùng **App Password** (mật khẩu ứng dụng),
không dùng được mật khẩu đăng nhập Gmail thường.

```ini
MAIL_USERNAME=email_cua_ban@gmail.com
MAIL_PASSWORD=app_password_16_ky_tu
```

### Sao lưu và khôi phục database

Chức năng này gọi `mysqldump` và `mysql`. Khai báo đường dẫn trong `.env`:

```ini
BACKUP_PATH=D:/Backups
MYSQLDUMP_PATH="C:/Program Files/MySQL/MySQL Server 8.0/bin/mysqldump.exe"
MYSQL_PATH="C:/Program Files/MySQL/MySQL Server 8.0/bin/mysql.exe"
```

Sao lưu bằng lệnh: `php artisan backup:run`

Sao lưu tự động hằng ngày: vào **Admin → Sao lưu & Khôi phục**, chọn giờ và bật, sau đó chạy lịch nền bằng
một trong hai cách:

- Mở một cửa sổ terminal riêng, chạy `php artisan schedule:work` và giữ cửa sổ đó mở.
- Hoặc tạo tác vụ trong Windows Task Scheduler gọi `php artisan schedule:run` mỗi phút.

## Chạy test

```bash
php artisan test
```

104 test kiểm tra: đăng nhập, đăng ký, OTP, giỏ hàng, đặt hàng, hủy đơn, báo cáo, quản lý sản phẩm, đơn hàng,
người dùng, sao lưu và khôi phục.

Test dùng database riêng `fashionstore_testing` (khai báo trong `phpunit.xml`) và tự tạo lại dữ liệu mỗi lần
chạy, nên **không ảnh hưởng database chính**. Tạo database test bằng `php create_db.php`.

## Cấu trúc thư mục

```
app/
  Enums/            OrderStatus, PaymentMethod, PaymentStatus, ShippingMethod
  Helpers/          CustomerSession (phiên đăng nhập), CartSessionHelper, PasswordHasher
  Http/
    Controllers/    Trang cửa hàng
    Controllers/Admin/  Trang quản trị
    Middleware/     Kiểm tra đăng nhập và quyền quản trị
  Models/           15 model Eloquent, đặt tên theo đúng bảng của SQL Server
  Rules/            Kiểm tra họ tên và số điện thoại Việt Nam
  Services/         Nghiệp vụ: đơn hàng, giỏ hàng, phí ship, kiểm tra thanh toán, ảnh, sao lưu
database/
  migrations/       15 bảng
  seeders/          Dữ liệu mẫu
public/Content/     CSS, JS, hình ảnh (giữ nguyên đường dẫn của bản gốc)
resources/views/    Giao diện Blade
tests/Feature/      Test tự động
```

## Xử lý sự cố

| Lỗi | Cách xử lý |
|---|---|
| `'php' is not recognized` | Thêm thư mục cài PHP vào biến môi trường PATH, rồi **mở lại** terminal. Với VS Code phải tắt hẳn rồi mở lại. |
| `Access denied for user 'root'@'localhost'` | Sai `DB_PASSWORD` trong `.env`. |
| `Unknown database 'fashionstore'` | Chạy `php create_db.php`. |
| `Could not open input file: artisan` | Đang đứng sai thư mục. `cd` vào thư mục gốc dự án. |
| Trang lỗi 500 sau khi sửa `.env` | Chạy `php artisan config:clear`. |
| Không gửi được OTP | Chưa điền `MAIL_USERNAME` / `MAIL_PASSWORD`, hoặc Gmail chưa tạo App Password. |
| Sao lưu báo `mysqldump ... error 2004` | Sai `MYSQLDUMP_PATH`, hoặc MySQL chưa chạy. |
| Giao diện mất CSS | Kiểm tra thư mục `public/Content` còn đủ file không. |

## Tài liệu khác

- [SO_SANH_CHUC_NANG.md](SO_SANH_CHUC_NANG.md) — bảng đối chiếu chức năng giữa bản C# và bản Laravel, kèm các
  lỗi của bản gốc đã được sửa.
- [MIGRATION_PROGRESS.md](MIGRATION_PROGRESS.md) — nhật ký quá trình chuyển đổi theo từng giai đoạn.

Hai việc còn lại cần bạn làm
Điền MAIL_USERNAME và MAIL_PASSWORD trong .env để chức năng quên mật khẩu gửi được OTP. Gmail phải dùng App Password.
Mở web bằng trình duyệt và xem giao diện. Tôi kiểm tra chức năng qua HTTP và test tự động, nhưng chưa nhìn thấy giao diện hiển thị thật, nhất là các biểu đồ trong trang quản trị. Nếu thấy chỗ nào lệch so với bản C#, bạn báo tôi sửa.
Chạy thử: php artisan serve, rồi đăng nhập admin / 123456 hoặc nguyenvana / 123456.
