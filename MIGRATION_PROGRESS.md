# Tiến độ chuyển đổi FashionStore: ASP.NET MVC 5 sang Laravel 13

- Mã nguồn gốc: `D:\Code\MoNguonMo\DoAn\FashionStore` (chỉ đọc, không sửa)
- Dự án mới: `D:\Code\MoNguonMo\DoAn\FashionStore-Laravel`
- Môi trường: PHP 8.4.25 (`C:\php84`), Composer 2.10.3 (`C:\composer`), MySQL 8.0 (dịch vụ `MySQL80`)

## Quy ước đã chốt
- **Tên bảng và cột giữ nguyên như SQL Server** (`Customers.CustomerID`, `Products.ProductName`...). Model Eloquent khai báo `$table` và `$primaryKey`, `$timestamps = false`.
- Mật khẩu dùng `App\Helpers\PasswordHasher`, tương thích 100% với `PasswordHasher.cs`: base64(salt 16 byte + HMACSHA256). Đã kiểm tra chéo với .NET.
- Role: 1 = Admin, 2 = Nhân viên, 3 = Khách hàng. Đăng nhập với RoleID 1 hoặc 2 thì chuyển tới Admin.
- Session: khóa `Customer` và `RoleID` tương tự bản C#. Session/cache lưu bằng file.
- Tài nguyên tĩnh giữ nguyên đường dẫn: `public/Content`, `public/Scripts`, `public/fonts`.
- Ảnh sản phẩm: `public/Content/img/products/product_001..060.jpg` (3 ảnh/sản phẩm, 900x1200), `default.jpg`, cùng `banner-image1.jpg` và `error-image.jpg` trong `public/Content/img/`. Ảnh tải từ Unsplash (Unsplash License: miễn phí, dùng thương mại được, không bắt buộc ghi nguồn).
- Sao lưu: `mysqldump`/`mysql`, đường dẫn cấu hình trong `.env` (`MYSQLDUMP_PATH`, `BACKUP_PATH`...).

## Quy ước code (áp dụng cho giai đoạn 3 trở đi)
| C# | Laravel |
|---|---|
| `Session["Customer"]`, `Session["RoleID"]` | `App\Helpers\CustomerSession::user() / id() / check() / isAdmin() / login() / logout()` |
| `Session["Cart"]` (List<CartItemViewModel>) | `App\Helpers\CartSessionHelper` (session `Cart`, mỗi dòng `['ProductID','VariantID','Quantity']`) |
| `TempData["Success"]`, `TempData["Error"]` | `->with('Success', ...)`, `->with('Error', ...)` (partial `partials.toast` tự hiển thị) |
| `[CustomAuthorize]` | middleware `customer.auth` |
| Khu Admin | middleware `admin` (RoleID 1/2, bản gốc không kiểm tra) |
| `ViewBag.Title`, `@RenderBody`, `@section scripts` | `@section('title')`, `@section('content')`, `@section('styles')`, `@section('scripts')` |
| Layout `_Layout.cshtml` | `@extends('layouts.app')` (view composer tự truyền menu danh mục, số giỏ hàng, `$currentCustomer`) |
| `_Product.cshtml` | `@include('partials.product-card', ['product' => $p])` (eager load `productImages`) |
| `Url.Action("Details","Product",new{id})` | `route('product.details', $id)`. Tên route xem `routes/web.php`, URL giữ nguyên như bản gốc |
| `x.ToString("N0") + "đ"` | `vnd($x)`; chỉ số: `n0($x)` |
| `@Html.AntiForgeryToken()` | `@csrf`. AJAX đã có header `X-CSRF-TOKEN` trong layout |
| Enum `OrderStatus` + switch hiển thị | `App\Enums\OrderStatus::parse()`, `->label()`, `->badgeClass()`, `OrderStatus::labelOf($str)` |
| `InvalidOperationException` | `App\Exceptions\InvalidOperationException` |
| URL sai hoa thường / thiếu action | `LegacyUrlController@fallback` tự chuyển hướng 301, không tìm thấy thì hiện `errors/404` |

## Tài khoản mẫu (sau khi seed)
- Admin: `admin` / `123456`
- Khách hàng: `nguyenvana`, `tranthib`, ... / `123456`

## Giai đoạn
- [x] **0. Môi trường**: PHP 8.4, Composer, PATH
- [x] **1. Khởi tạo và Database**: dự án Laravel 13.31, `.env`, 15 migrations, seeder dữ liệu mẫu, tài nguyên tĩnh, 64 ảnh sản phẩm
  - Đã chạy `migrate --seed` trên database `fashionstore` (MySQL 8.0.44): 51 tài khoản, 20 sản phẩm, 250 biến thể, 60 ảnh, 50 đơn, 124 chi tiết đơn, 30 giỏ hàng. Tiếng Việt, SKU, tổng tiền đơn và đăng nhập đều đúng.
  - Seed lại từ đầu: `php artisan migrate:fresh --seed`
- [x] **2. Nền tảng**: Models, Services (Order, Shipping, CheckoutValidation), CartSessionHelper, Validation Rules (ValidFullName, VietnamesePhone), middleware đăng nhập/admin, layout Blade + partials
  - 28 test đạt (`php artisan test`, chạy trên DB riêng `fashionstore_testing`)
  - `php artisan route:list` sẽ báo lỗi cho tới khi tạo đủ controller ở giai đoạn 3-6 (routes đã khai báo sẵn toàn bộ)
- [x] **3. Khách hàng (1)**: Home, Product, Account, ForgotPassword + Mail, Error/NotFound
  - `CartService` (giỏ hàng lưu DB, cookie `CartToken` cho khách vãng lai, gộp giỏ khi đăng nhập) dùng lại ở giai đoạn 4
  - Tổng 54 test đạt. Đã chạy thử `php artisan serve`: các trang trả về 200, POST không có CSRF bị chặn (419), đăng nhập thật thành công
  - Chưa dùng được: nút "Thêm vào giỏ" (giai đoạn 4), đăng nhập Admin chuyển tới Dashboard (giai đoạn 5)
  - Gửi OTP cần điền `MAIL_USERNAME` và `MAIL_PASSWORD` (App Password của Gmail) trong `.env`
  - Khác bản gốc: phải xác thực OTP mới được đổi mật khẩu, giới hạn nhập sai OTP / gửi OTP / đăng nhập sai; sửa hồ sơ luôn theo tài khoản đang đăng nhập (bản gốc lấy ProfileID từ form); đăng ký kiểm tra dữ liệu (mật khẩu tối thiểu 6 ký tự); phân trang giữ bộ lọc; trang hồ sơ chọn sẵn tỉnh/quận đã lưu
- [x] **4. Khách hàng (2)**: Cart, Checkout, Order, ThongKe
  - Tổng 71 test đạt. Đã chạy thử qua HTTP: thêm giỏ (khách vãng lai) → sửa số lượng (AJAX) → đăng nhập gộp giỏ → đặt hàng → hủy đơn → xem thống kê
  - Khác bản gốc: tổng tiền checkout luôn tính lại ở server (bản gốc không gửi tổng tiền lên nên bước kiểm tra tổng tiền không có tác dụng); phí ship và tổng tiền cập nhật ngay khi đổi tỉnh; chặn đặt sản phẩm đã ngừng kinh doanh; lỗi checkout giữ lại dữ liệu đã nhập; xóa khỏi giỏ bằng POST (bản gốc dùng link GET); không tạo giỏ rỗng khi chỉ xem trang giỏ hàng; khóa đơn khi hủy để không hoàn kho 2 lần; trang ThongKe bắt buộc đăng nhập, nút hủy ở trang chi tiết hoạt động (bản gốc chưa làm), thêm link "Lịch sử mua hàng" vào menu; bỏ nút "Đánh giá" và "Mua lại" vì bản gốc chưa có chức năng (dẫn tới trang 404); thêm phí vận chuyển vào bảng chi tiết đơn
- [x] **5. Admin (1)**: layout Admin, Dashboard + 4 báo cáo, Category, User (thay `sp_getAllUser`)
  - Tổng 84 test đạt. Đã chạy thử qua HTTP: đăng nhập admin, 5 trang báo cáo, 8 API biểu đồ, thêm danh mục, lọc người dùng
  - Dữ liệu mẫu có ngày đặt hàng tháng 11/2024: muốn thấy biểu đồ, chọn khoảng ngày chứa tháng 11/2024
  - Phân quyền: Admin (1) và Nhân viên (2) vào được Dashboard, Sản phẩm, Danh mục, Đơn hàng; chỉ Admin dùng được Người dùng và Sao lưu / Khôi phục (middleware `admin.only`)
  - Khác bản gốc: kiểm tra quyền ở server (bản gốc chỉ ẩn menu); khoá tài khoản / đổi vai trò / thêm, sửa, xoá danh mục dùng POST có CSRF (bản gốc dùng link GET, form không có token); không cho Admin tự khoá hoặc tự đổi vai trò; không cho xoá 3 vai trò hệ thống; kiểm tra trùng tên danh mục trong cùng nhóm; sửa nút "Hủy" trong modal danh mục bị submit form; sửa thẻ "Sản Phẩm" trên Dashboard không hiện số (lỗi `@ViewBag.TotalProductsh4`); lọc ngày của bảng sản phẩm tính tới hết ngày; tìm người dùng theo cả username, họ tên, email (không phân biệt hoa thường); bỏ các phần giả trên trang Người dùng (modal thêm/sửa người dùng, phân trang, thao tác hàng loạt chưa hoạt động); chống XSS khi dựng bảng bằng JavaScript; thêm link "Về trang cửa hàng"
- [x] **6. Admin (2)**: Product (tạo/sửa/ảnh), Order + lịch sử trạng thái, Backup/Restore + Scheduler
  - Tổng 104 test đạt, trong đó sao lưu / khôi phục chạy `mysqldump` / `mysql` thật (khôi phục thử trên database riêng `fashionstore_restore_test`, tự xóa sau test)
  - Đã chạy thử qua HTTP: thêm sản phẩm có upload ảnh, sửa, xóa; đổi trạng thái đơn có ghi chú; sao lưu ngay và khôi phục
  - Sao lưu: file `.sql` lưu ở `BACKUP_PATH` (.env, hiện là `D:/Backups`). Cài đặt tự động lưu ở `storage/app/backup-settings.json`. Lịch tự động cần chạy `php artisan schedule:work` (hoặc Windows Task Scheduler gọi `php artisan schedule:run` mỗi phút). Lệnh tay: `php artisan backup:run`
  - Khác bản gốc:
    - Sản phẩm: tên file ảnh sinh ngẫu nhiên (bản gốc lưu theo tên file người dùng nên ảnh trùng tên ghi đè nhau, có thể ghi file tùy ý); chỉ nhận JPG/PNG/WEBP ≤ 5MB; tối đa 4 ảnh phụ ở cả trang thêm và trang ảnh (bản gốc ghi 10 ở trang thêm nhưng giới hạn 4 ở trang ảnh, và kiểm tra giới hạn trước khi thêm nên vẫn vượt được); thay ảnh chính thì xóa file cũ; xóa ảnh phụ thì xóa file; không cho xóa ảnh chính; kiểm tra dữ liệu (giá, % giảm, danh mục, biến thể trùng màu+size, SKU trùng); không xóa được sản phẩm đã có đơn hàng (bản gốc báo lỗi database rồi im lặng); sửa lỗi trang Sửa gửi tên size thay cho SizeID; bỏ nút size "XXL" gắn cứng không có ID; tên trường biến thể đổi sang `Variants[i][Field]` vì PHP không đọc được `Variants[i].Field`; bỏ các `setTimeout` chờ 0,5-2 giây trước khi gửi form
    - Đơn hàng: timeline lấy từ bảng `OrderStatusHistory` (bản gốc tự bịa mốc giờ +15 phút, +1 ngày); ghi người đổi là tài khoản admin (bản gốc luôn là "Admin"); ô ghi chú dùng được (bản gốc bị disabled); modal chỉ hiện các trạng thái được phép chuyển; khóa dòng đơn khi đổi trạng thái; thống kê số đơn theo trạng thái tính trên toàn bộ đơn (bản gốc chỉ đếm 10 đơn của trang hiện tại); nút "Gửi email" / "Gọi điện" dùng `mailto:` / `tel:`; bỏ nút "Xuất Excel" giả
    - Sao lưu: bản gốc không có view nên trang bị lỗi, và dùng lệnh `BACKUP DATABASE` của SQL Server; bản mới có giao diện đầy đủ, danh sách bản sao lưu (tải về / khôi phục), chỉ nhận file do mysqldump tạo, tự sao lưu dữ liệu hiện tại trước khi khôi phục; sao lưu tự động chạy bằng scheduler (bản gốc chỉ chạy khi admin mở trang đúng phút đã cài); truyền `SYSTEMROOT` cho tiến trình con trên Windows (thiếu thì mysqldump lỗi 2004 khi chạy qua web server)
- [x] **7. Hoàn thiện**: rà soát, so sánh chức năng, README
  - `README.md` (cài đặt, cấu hình, xử lý sự cố) và `SO_SANH_CHUC_NANG.md` (đối chiếu chức năng + 35 lỗi của bản gốc đã sửa)
  - Gỡ phần scaffolding Vite/npm không dùng (`package.json`, `vite.config.js`, `resources/css`, `resources/js`), sửa lệnh `composer setup` cho đúng dự án
  - Đã quét toàn bộ route GET bằng tài khoản khách và admin: không trang nào lỗi. `view:cache`, `route:cache`, `config:cache` đều chạy được (sẵn sàng chạy production)
  - Kết quả cuối: 15 controller, 16 model, 41 view, 104 test đạt

## Ghi chú kỹ thuật
- MySQL trên Windows mặc định `lower_case_table_names=1`, nên tên bảng được lưu chữ thường. Truy vấn không phân biệt hoa thường, không ảnh hưởng code.
- File SQL gốc thiếu bảng `Roles`, cột `Customers.RoleID`, `CartItems.AddedAt` và bảng `OrderStatusHistory`. Đã bổ sung theo EF model (`FashionStore.edmx`).
- `database/seeders/sql/fashionstore_data.sql` được sinh tự động từ file SQL gốc (bỏ tiền tố `N'`, `GETDATE()` thành `NOW()`).
