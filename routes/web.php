<?php

/*
| Giữ nguyên cấu trúc URL của bản ASP.NET MVC: /{Controller}/{Action}/{id?}
| Khu quản trị: /Admin/{Controller}/{Action}/{id?}
| Action không có [HttpPost] trong C# nhận cả GET lẫn POST nên dùng Route::match.
*/

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegacyUrlController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ThongKeController;
use Illuminate\Support\Facades\Route;

$any = ['GET', 'POST'];

// ===== Trang chủ =====
Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/Home/Index', [HomeController::class, 'index']);
Route::get('/Home/About', [HomeController::class, 'about'])->name('home.about');
Route::get('/Error/NotFound', [HomeController::class, 'notFound'])->name('error.not-found');

// ===== Sản phẩm =====
Route::get('/Product/Index', [ProductController::class, 'index'])->name('product.index');
Route::get('/Product/Details/{id?}', [ProductController::class, 'details'])->name('product.details');

// ===== Tài khoản =====
Route::controller(AccountController::class)->prefix('Account')->name('account.')->group(function () {
    Route::get('/Login', 'login')->name('login');
    Route::post('/Login', 'loginPost')->name('login.post');
    Route::get('/Register', 'register')->name('register');
    Route::post('/Register', 'registerPost')->name('register.post');
    Route::get('/Logout', 'logout')->name('logout');

    Route::middleware('customer.auth')->group(function () {
        Route::get('/Index', 'index')->name('index');
        Route::post('/Update', 'update')->name('update');
    });
});

// ===== Quên mật khẩu (OTP qua email) =====
Route::controller(ForgotPasswordController::class)->prefix('ForgotPassword')->name('forgot.')->group(function () {
    Route::get('/Index', 'index')->name('index');
    Route::post('/SendOTP', 'sendOtp')->name('send-otp');
    Route::post('/VerifyOTP', 'verifyOtp')->name('verify-otp');
    Route::post('/ResetPassword', 'resetPassword')->name('reset');
});

// ===== Giỏ hàng =====
Route::controller(CartController::class)->prefix('Cart')->name('cart.')->group(function () {
    Route::get('/Index', 'index')->name('index');
    Route::post('/AddToCart', 'addToCart')->name('add');
    Route::post('/Remove/{id?}', 'remove')->name('remove');
    Route::post('/UpdateQuantity', 'updateQuantity')->name('update-quantity');
    Route::post('/Clear', 'clear')->name('clear');

    Route::middleware('customer.auth')->group(function () {
        Route::get('/Checkout', 'checkout')->name('checkout');
        Route::post('/Checkout', 'checkoutPost')->name('checkout.post');
    });
});

// ===== Đơn hàng của khách =====
Route::controller(OrderController::class)->prefix('Order')->name('order.')->middleware('customer.auth')->group(function () {
    Route::get('/Index', 'index')->name('index');
    Route::post('/Cancel/{id?}', 'cancel')->name('cancel');
});

// ===== Thống kê mua hàng của khách =====
Route::controller(ThongKeController::class)->prefix('ThongKe')->name('thongke.')->middleware('customer.auth')->group(function () {
    Route::get('/Index', 'index')->name('index');
    Route::get('/Details/{id}', 'details')->name('details');
});

// ===== Khu quản trị =====
Route::prefix('Admin')->name('admin.')->middleware('admin')->group(function () use ($any) {
    Route::controller(Admin\DashboardController::class)->prefix('Dashboard')->name('dashboard.')->group(function () use ($any) {
        Route::get('/Index', 'index')->name('index');
        Route::get('/DonHangReport', 'donHangReport')->name('don-hang-report');
        Route::get('/CustomersReport', 'customersReport')->name('customers-report');
        Route::get('/RevenueReport', 'revenueReport')->name('revenue-report');
        Route::get('/SanPhamReport', 'sanPhamReport')->name('san-pham-report');
        Route::match($any, '/GetTopProductsReport', 'getTopProductsReport')->name('get-top-products-report');
        Route::match($any, '/GetTotalQtyTrend', 'getTotalQtyTrend')->name('get-total-qty-trend');
        Route::match($any, '/GetCustomerRetention', 'getCustomerRetention')->name('get-customer-retention');
        Route::match($any, '/GetCustomerSpendingByDay', 'getCustomerSpendingByDay')->name('get-customer-spending-by-day');
        Route::match($any, '/GetProductBreakdown', 'getProductBreakdown')->name('get-product-breakdown');
        Route::match($any, '/GetAllProductsReport', 'getAllProductsReport')->name('get-all-products-report');
        Route::match($any, '/RevenueByDay', 'revenueByDay')->name('revenue-by-day');
        Route::match($any, '/CustomersByDay', 'customersByDay')->name('customers-by-day');
        Route::match($any, '/OrdersByDay', 'ordersByDay')->name('orders-by-day');
        Route::match($any, '/TopProducts', 'topProducts')->name('top-products');
        Route::match($any, '/TopCustomers', 'topCustomers')->name('top-customers');
        Route::match($any, '/GetTop10SpendingCustomers', 'getTop10SpendingCustomers')->name('get-top10-spending-customers');
    });

    Route::controller(Admin\CategoryController::class)->prefix('Category')->name('category.')->group(function () {
        Route::get('/Index', 'index')->name('index');
        Route::post('/Create', 'create')->name('create');
        Route::post('/Update', 'update')->name('update');
        Route::post('/Delete/{id?}', 'delete')->name('delete');
    });

    Route::controller(Admin\ProductController::class)->prefix('Product')->name('product.')->group(function () {
        Route::get('/Index', 'index')->name('index');
        Route::get('/Create', 'create')->name('create');
        Route::post('/Create', 'store')->name('store');
        Route::get('/Edit/{id}', 'edit')->name('edit');
        Route::post('/Edit/{id}', 'updateProduct')->name('update');
        Route::post('/EditStatus', 'editStatus')->name('edit-status');
        Route::post('/EditManyStatus', 'editManyStatus')->name('edit-many-status');
        Route::get('/EditImages/{id}', 'editImages')->name('edit-images');
        Route::post('/EditImages/{id}', 'updateImages')->name('update-images');
        Route::post('/Delete/{id?}', 'delete')->name('delete');
        Route::post('/DeleteMany', 'deleteMany')->name('delete-many');
        Route::post('/DeleteImage', 'deleteImage')->name('delete-image');
    });

    Route::controller(Admin\OrderController::class)->prefix('Order')->name('order.')->group(function () {
        Route::get('/Index', 'index')->name('index');
        Route::get('/Details/{id}', 'details')->name('details');
        Route::post('/EditStatus/{id?}', 'editStatus')->name('edit-status');
        Route::post('/EditManyStatus', 'editManyStatus')->name('edit-many-status');
    });

    // Quản lý người dùng và sao lưu / khôi phục: chỉ Admin (RoleID 1)
    Route::controller(Admin\UserController::class)->prefix('User')->name('user.')->middleware('admin.only')->group(function () {
        Route::get('/Index', 'index')->name('index');
        Route::get('/getAllUser', 'getAllUser')->name('get-all-user');
        Route::post('/UpdateState', 'updateState')->name('update-state');
        Route::post('/ChangeRole', 'changeRole')->name('change-role');
        Route::post('/addRole', 'addRole')->name('add-role');
        Route::post('/dropRole', 'dropRole')->name('drop-role');
    });

    Route::controller(Admin\BackupController::class)->prefix('Backup')->name('backup.')->middleware('admin.only')->group(function () {
        Route::get('/Index', 'index')->name('index');
        Route::post('/Action', 'action')->name('action');
        Route::post('/Export', 'export')->name('export');
        Route::get('/Download/{file}', 'download')->where('file', '[A-Za-z0-9_\-]+\.sql')->name('download');
    });
});

// URL không khớp chính xác: thử khớp không phân biệt hoa thường (/product/details/1, /Product, /Admin...)
Route::fallback([LegacyUrlController::class, 'fallback']);
