# So sánh bản gốc (ASP.NET MVC 5) và bản chuyển đổi (Laravel 13)

Tài liệu đối chiếu từng chức năng giữa hai bản, kèm danh sách lỗi của bản gốc đã được sửa.

- Bản gốc: ASP.NET MVC 5, Entity Framework, SQL Server
- Bản mới: Laravel 13.31, Eloquent ORM, MySQL 8, PHP 8.4

## 1. Đối chiếu kỹ thuật

| Thành phần | Bản gốc (C#) | Bản mới (Laravel) |
|---|---|---|
| Controller | `Controllers/*.cs` | `app/Http/Controllers/*.php` |
| Khu quản trị | `Areas/Admin` | Nhóm route `/Admin` + middleware `admin` |
| Truy cập dữ liệu | Entity Framework (`FashionStoreEntities`) | Eloquent (`app/Models`) |
| Giao diện | Razor `.cshtml` | Blade `.blade.php` |
| Layout | `_Layout.cshtml` | `layouts/app.blade.php`, `layouts/admin.blade.php` |
| Phiên đăng nhập | `Session["Customer"]` | `App\Helpers\CustomerSession` (session chỉ lưu ID, đọc lại từ DB mỗi request) |
| Giỏ hàng | Bảng `Carts` + cookie `CartToken` + `Session["Cart"]` | Giữ nguyên cách làm, gom vào `App\Services\CartService` |
| Mật khẩu | `PasswordHasher.cs` (HMACSHA256 + salt) | `App\Helpers\PasswordHasher` (giống hệt, tài khoản cũ đăng nhập được) |
| Kiểm tra dữ liệu | Data Annotation, `ValidFullNameAttribute`, `VietnamesePhoneAttribute` | Validation của Laravel + `App\Rules\ValidFullName`, `VietnamesePhone` |
| Phân quyền | `[CustomAuthorize]` | Middleware `customer.auth`, `admin`, `admin.only` |
| Thông báo | `TempData["Success"]` / `["Error"]` | `session()->flash('Success' / 'Error')` |
| Lấy danh sách người dùng | Stored procedure `sp_getAllUser` | Truy vấn Eloquent trong `Admin\UserController` |
| Sao lưu | `BACKUP DATABASE` (SQL Server, file `.bak`) | `mysqldump` / `mysql` (file `.sql`) |
| Sao lưu tự động | Ghi giờ vào `Web.config`, chỉ chạy khi admin mở trang | Laravel Scheduler (`php artisan schedule:work`) |
| Kiểm thử | Không có | 104 test tự động (`php artisan test`) |

## 2. Đối chiếu chức năng

Tất cả chức năng của bản gốc đều đã có trong bản mới.

### Trang cửa hàng

| Chức năng | Bản gốc | Bản mới |
|---|---|---|
| Trang chủ, giới thiệu | Có | Có |
| Danh sách sản phẩm: lọc danh mục, tìm kiếm, lọc giá, sắp xếp, phân trang | Có | Có |
| Chi tiết sản phẩm, sản phẩm liên quan | Có | Có |
| Đăng ký, đăng nhập, đăng xuất | Có | Có |
| Quên mật khẩu qua OTP email | Có | Có |
| Thông tin cá nhân | Có | Có |
| Giỏ hàng: thêm, sửa số lượng, xóa | Có | Có |
| Gộp giỏ hàng khách vãng lai khi đăng nhập | Có | Có |
| Đặt hàng (COD), tính phí ship theo tỉnh | Có | Có |
| Đơn hàng của tôi, lọc theo trạng thái, hủy đơn | Có | Có |
| Lịch sử mua hàng (ThongKe) | Có | Có |
| Đánh giá sản phẩm, mua lại | Chỉ có nút, bấm vào lỗi 404 | Đã bỏ nút |

### Trang quản trị

| Chức năng | Bản gốc | Bản mới |
|---|---|---|
| Dashboard: doanh thu, đơn hàng, khách hàng, sản phẩm | Có | Có |
| Báo cáo đơn hàng, doanh thu, khách hàng, sản phẩm | Có | Có |
| Quản lý danh mục | Có | Có |
| Quản lý sản phẩm: thêm, sửa, ảnh, trạng thái, xóa | Có | Có |
| Quản lý đơn hàng: đổi trạng thái đơn lẻ và hàng loạt | Có | Có |
| Lịch sử trạng thái đơn hàng | Ghi vào DB nhưng không hiển thị | Hiển thị đầy đủ trên timeline |
| Quản lý người dùng, vai trò | Có | Có |
| Sao lưu và khôi phục | Có code nhưng **thiếu giao diện, trang bị lỗi** | Có, kèm danh sách bản sao lưu và tải về |
| Thêm/sửa người dùng bằng modal | Chỉ là giao diện mẫu, không lưu gì | Đã bỏ |
| Xuất Excel | Chỉ hiện thông báo giả | Đã bỏ |

## 3. Lỗi của bản gốc đã sửa

### 3.1. Bảo mật

| # | Vấn đề ở bản gốc | Cách sửa |
|---|---|---|
| 1 | Chức năng đặt lại mật khẩu không kiểm tra đã xác thực OTP chưa, nên chỉ cần biết email là đổi được mật khẩu người khác | Bắt buộc xác thực OTP đúng mới cho đổi |
| 2 | Không giới hạn số lần nhập sai OTP, số lần gửi OTP, số lần đăng nhập sai | Sai OTP 5 lần thì hủy mã; gửi OTP tối đa 3 lần/10 phút; đăng nhập sai 5 lần thì khóa tạm 1 phút |
| 3 | Trang cập nhật hồ sơ lấy `ProfileID` từ ô ẩn trên form, sửa số này là sửa được hồ sơ người khác | Luôn cập nhật hồ sơ của tài khoản đang đăng nhập |
| 4 | Controller khu Admin không kiểm tra quyền, chỉ ẩn menu; biết URL là vào được | Kiểm tra quyền ở server cho mọi chức năng |
| 5 | Khóa tài khoản, đổi vai trò, xóa khỏi giỏ hàng dùng link GET, không có CSRF token | Chuyển sang POST kèm CSRF |
| 6 | Upload ảnh lưu theo tên file người dùng gửi lên, ảnh trùng tên ghi đè nhau và có thể ghi đè file khác | Tên file sinh ngẫu nhiên, chỉ nhận JPG/PNG/WEBP tối đa 5MB |
| 7 | Chức năng khôi phục nhận mọi file | Chỉ nhận file `.sql` do mysqldump tạo, và tự sao lưu dữ liệu hiện tại trước khi khôi phục |
| 8 | Bảng dựng bằng JavaScript chèn thẳng tên sản phẩm, tên tài khoản vào HTML (nguy cơ XSS) | Mã hóa ký tự đặc biệt trước khi chèn |
| 9 | Admin có thể tự khóa hoặc tự hạ quyền chính mình, hoặc xóa vai trò hệ thống | Đã chặn |

### 3.2. Tính tiền và dữ liệu

| # | Vấn đề ở bản gốc | Cách sửa |
|---|---|---|
| 10 | Form checkout không gửi tổng tiền lên server nên bước kiểm tra tổng tiền luôn đúng, không có tác dụng | Server tự tính lại toàn bộ tiền từ giỏ hàng |
| 11 | Phí ship hiển thị theo tỉnh cũ trong hồ sơ, dù khách chọn tỉnh khác lúc đặt hàng | Cập nhật phí ship và tổng tiền ngay khi đổi tỉnh |
| 12 | Bấm hủy đơn liên tục có thể hoàn kho nhiều lần | Khóa dòng đơn hàng khi đổi trạng thái |
| 13 | Vẫn đặt được sản phẩm đã ngừng kinh doanh | Chặn khi thanh toán |
| 14 | Xóa sản phẩm đã có đơn hàng gây lỗi database rồi bị bỏ qua im lặng | Báo rõ lý do, gợi ý chuyển sang trạng thái Hết hàng |
| 15 | Trang sửa sản phẩm gửi tên size thay cho mã size | Gửi đúng mã |
| 16 | Giới hạn ảnh phụ không nhất quán (trang thêm ghi 10, trang ảnh giới hạn 4 nhưng vẫn vượt được) | Thống nhất tối đa 4 ảnh, kiểm tra ở server |
| 17 | Thống kê đơn hàng ở trang Admin chỉ đếm 10 đơn của trang đang xem | Đếm trên toàn bộ đơn hàng |
| 18 | Bộ lọc ngày của báo cáo sản phẩm bỏ sót dữ liệu ngày cuối | Tính tới hết ngày |
| 19 | Timeline đơn hàng hiển thị mốc giờ tự bịa (+15 phút, +1 ngày) | Lấy từ bảng `OrderStatusHistory` |
| 20 | Người đổi trạng thái luôn ghi là "Admin" | Ghi đúng tài khoản thực hiện |

### 3.3. Giao diện và trải nghiệm

| # | Vấn đề ở bản gốc | Cách sửa |
|---|---|---|
| 21 | Thẻ "Sản Phẩm" trên Dashboard không hiện số (gõ nhầm `@ViewBag.TotalProductsh4`) | Hiện đúng số sản phẩm |
| 22 | Nút "Hủy" trong modal danh mục lại gửi form đi | Đổi thành `type="button"` |
| 23 | Phân trang trang sản phẩm làm mất bộ lọc đang chọn | Giữ nguyên bộ lọc |
| 24 | Dòng "Hiển thị 1–9" sai khi ở trang 2 trở đi | Hiện đúng khoảng sản phẩm |
| 25 | Trang hồ sơ không chọn sẵn tỉnh/quận đã lưu, phải chọn lại mỗi lần sửa | Chọn sẵn giá trị đã lưu |
| 26 | Trang giỏ hàng ghi "Phí vận chuyển: Miễn phí" nhưng khi thanh toán lại tính phí | Ghi "Tính khi thanh toán" |
| 27 | Trang ThongKe lỗi khi chưa đăng nhập; nút "Hủy đơn hàng" bấm không có tác dụng; không có link vào trang | Bắt buộc đăng nhập, nút hủy hoạt động, thêm link vào menu tài khoản |
| 28 | Tiêu đề trang hồ sơ gõ nhầm thành "sThông tin cá nhân" | Đã sửa |
| 29 | Ô ghi chú khi đổi trạng thái đơn bị khóa | Nhập được, ghi chú lưu vào lịch sử |
| 30 | Các form chờ 0,5–2 giây rồi mới gửi (dễ bấm trùng) | Gửi ngay, chặn bấm trùng |
| 31 | Trang Người dùng có phân trang giả, thanh thao tác hàng loạt và modal không hoạt động | Đã bỏ |
| 32 | Đăng ký tài khoản không kiểm tra dữ liệu | Kiểm tra email, tên đăng nhập, mật khẩu tối thiểu 6 ký tự |
| 33 | Đổi trạng thái đơn hàng phải qua modal mở bằng JavaScript; đổi vai trò và khoá tài khoản phải bấm vào badge mới hiện ô chọn (không có gợi ý nào trên giao diện) | Ô chọn trạng thái, ô chọn vai trò và nút Khoá/Mở khoá hiện sẵn trên từng dòng, gửi bằng form thường nên chạy cả khi JavaScript lỗi |
| 34 | Enum `PaymentStatus` khai báo nhưng không dùng ở đâu (bảng `Orders` không có cột này) | Đã xoá khỏi bản Laravel |

### 3.4. Vấn đề riêng khi chạy trên PHP/Windows

| # | Vấn đề | Cách sửa |
|---|---|---|
| 35 | PHP không đọc được tên trường kiểu ASP.NET `Variants[0].ColorID` | Đổi sang `Variants[0][ColorID]` |
| 36 | URL của Laravel phân biệt hoa thường, khác ASP.NET | Thêm `LegacyUrlController` tự chuyển hướng về đúng URL |
| 37 | `mysqldump` lỗi 2004 khi chạy qua web server trên Windows | Truyền biến môi trường `SYSTEMROOT` cho tiến trình con |

## 4. Bổ sung thêm so với bản gốc

- 104 test tự động cho toàn bộ luồng nghiệp vụ chính.
- Danh sách bản sao lưu kèm nút tải về và khôi phục trực tiếp.
- Vai trò Nhân viên (RoleID 2) được phân quyền rõ ràng: không vào được Người dùng và Sao lưu.
- Trang Sao lưu có 4 thẻ thống kê (số bản sao lưu, tổng dung lượng, bản gần nhất, trạng thái lịch tự động) và
  nhãn phân loại từng bản sao lưu: Thủ công / Tự động / Trước khi khôi phục.
- Link "Về trang cửa hàng" trong menu quản trị và link "Lịch sử mua hàng" trong menu tài khoản.
