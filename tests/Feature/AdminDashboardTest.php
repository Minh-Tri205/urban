<?php

namespace Tests\Feature;

use App\Helpers\PasswordHasher;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private Customer $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Customer::where('UserName', 'admin')->first();
    }

    private function asAdmin(): static
    {
        return $this->withSession(['CustomerID' => $this->admin->CustomerID]);
    }

    private function makeStaff(): Customer
    {
        return Customer::create([
            'UserName' => 'nhanvien1',
            'Email' => 'nhanvien1@example.com',
            'PasswordHash' => PasswordHasher::hashPassword('123456'),
            'IsActive' => true,
            'RoleID' => Role::STAFF,
            'CreatedAt' => now(),
        ]);
    }

    // ===== Phân quyền =====

    public function test_admin_area_requires_admin_or_staff(): void
    {
        $this->get('/Admin/Dashboard/Index')->assertRedirect(route('account.login'));

        $customer = Customer::where('RoleID', Role::CUSTOMER)->first();
        $this->withSession(['CustomerID' => $customer->CustomerID])
            ->get('/Admin/Dashboard/Index')
            ->assertRedirect(route('home.index'));

        $this->flushSession();
        $staff = $this->makeStaff();
        $this->withSession(['CustomerID' => $staff->CustomerID])
            ->get('/Admin/Dashboard/Index')
            ->assertOk()
            ->assertSee('Xin chào nhanvien1')
            ->assertDontSee(route('admin.user.index'), false);

        $this->withSession(['CustomerID' => $staff->CustomerID])
            ->get('/Admin/User/Index')
            ->assertRedirect(route('admin.dashboard.index'))
            ->assertSessionHas('Error', 'Chỉ quản trị viên mới được dùng chức năng này.');

        $this->withSession(['CustomerID' => $staff->CustomerID])
            ->post('/Admin/User/ChangeRole', ['CustomerID' => $staff->CustomerID, 'RoleID' => Role::ADMIN])
            ->assertRedirect(route('admin.dashboard.index'));
        $this->assertSame(Role::STAFF, $staff->fresh()->RoleID);
    }

    public function test_admin_url_without_action_goes_to_dashboard(): void
    {
        $this->asAdmin()->get('/admin')->assertRedirect(url('/Admin/Dashboard/Index'));
    }

    // ===== Dashboard =====

    public function test_dashboard_index_shows_statistics(): void
    {
        $from = now()->subDays(7)->startOfDay();
        $to = now()->endOfDay();

        $response = $this->asAdmin()->get('/Admin/Dashboard/Index')->assertOk()
            ->assertSee('Thống kê & Báo cáo', false)
            ->assertSee(route('admin.user.index'), false);

        $this->assertEquals(
            (float) Order::where('Status', 'Completed')->whereBetween('OrderDate', [$from, $to])->sum('TotalAmount'),
            $response->viewData('totalRevenue')
        );
        $this->assertSame(Order::whereBetween('OrderDate', [$from, $to])->count(), $response->viewData('totalOrders'));
        $this->assertSame(Customer::where('RoleID', 3)->count(), $response->viewData('totalCustomers'));
        $this->assertSame(20, $response->viewData('totalProducts'));
    }

    public function test_dashboard_date_filter_and_invalid_dates(): void
    {
        $response = $this->asAdmin()->get('/Admin/Dashboard/Index?fromDate=2020-01-01&toDate=2030-12-31')->assertOk();
        $this->assertSame(Order::count(), $response->viewData('totalOrders'));
        $this->assertEquals((float) Order::where('Status', 'Completed')->sum('TotalAmount'), $response->viewData('totalRevenue'));

        // Đảo ngày khi từ ngày > đến ngày
        $swapped = $this->asAdmin()->get('/Admin/Dashboard/Index?fromDate=2030-12-31&toDate=2020-01-01');
        $this->assertSame('2020-01-01', $swapped->viewData('fromDate'));

        $invalid = $this->asAdmin()->get('/Admin/Dashboard/Index?fromDate=abc&toDate=2024-02-31')->assertOk();
        $this->assertSame(now()->toDateString(), $invalid->viewData('toDate'));
    }

    public function test_report_pages_render(): void
    {
        $range = '?fromDate=2020-01-01&toDate=2030-12-31';

        $orders = $this->asAdmin()->get('/Admin/Dashboard/DonHangReport' . $range)->assertOk()->assertSee('BÁO CÁO ĐƠN HÀNG');
        $this->assertCount(Order::count(), $orders->viewData('orders'));

        $revenue = $this->asAdmin()->get('/Admin/Dashboard/RevenueReport' . $range)->assertOk()->assertSee('BÁO CÁO DOANH THU');
        $this->assertCount(Order::where('Status', 'Completed')->count(), $revenue->viewData('orders'));

        $customers = $this->asAdmin()->get('/Admin/Dashboard/CustomersReport' . $range)->assertOk()->assertSee('PHÂN TÍCH CHI TIÊU');
        $this->assertCount(Customer::where('RoleID', 3)->count(), $customers->viewData('customers'));
        $first = $customers->viewData('customers')->firstWhere('completed_orders', '>', 0);
        $this->assertEquals(
            (float) Order::where('CustomerID', $first->CustomerID)->where('Status', 'Completed')->sum('TotalAmount'),
            (float) $first->total_spent
        );

        $this->asAdmin()->get('/Admin/Dashboard/SanPhamReport' . $range)->assertOk()->assertSee('BÁO CÁO CHI TIẾT SẢN PHẨM');
    }

    public function test_chart_json_endpoints_match_database(): void
    {
        $range = ['fromDate' => '2020-01-01', 'toDate' => '2030-12-31'];
        $completedQty = (int) OrderDetail::whereHas('order', fn ($q) => $q->where('Status', 'Completed'))->sum('Quantity');
        $completedRevenue = (float) Order::where('Status', 'Completed')->sum('TotalAmount');

        $revenue = $this->asAdmin()->getJson(route('admin.dashboard.revenue-by-day', $range))->assertOk()->json();
        $this->assertEqualsWithDelta($completedRevenue, array_sum(array_column($revenue, 'Total')), 0.01);
        $this->assertMatchesRegularExpression('#^\d{2}/\d{2}/\d{4}$#', $revenue[0]['Date']);

        $trend = $this->asAdmin()->getJson(route('admin.dashboard.get-total-qty-trend', $range))->json();
        $this->assertSame($completedQty, array_sum(array_column($trend, 'Qty')));

        $ordersByDay = $this->asAdmin()->getJson(route('admin.dashboard.orders-by-day', $range))->json();
        $this->assertSame(Order::count(), collect($ordersByDay)->sum(fn ($d) => $d['Pending'] + $d['Processing'] + $d['Shipped'] + $d['Completed'] + $d['Cancelled']));

        $breakdown = $this->asAdmin()->getJson(route('admin.dashboard.get-product-breakdown', $range))->json();
        $this->assertSame($completedQty, collect($breakdown['datasets'])->sum(fn ($ds) => array_sum($ds['data'])));
        $this->assertCount(count($breakdown['labels']), $breakdown['datasets'][0]['data']);

        $all = $this->asAdmin()->getJson(route('admin.dashboard.get-all-products-report', $range))->json();
        $this->assertCount(20, $all);
        $this->assertSame($completedQty, array_sum(array_column($all, 'Qty')));
        $this->assertSame((int) DB::table('ProductVariants')->sum('Stock'), array_sum(array_column($all, 'Stock')));
        $this->assertGreaterThanOrEqual($all[1]['Qty'], $all[0]['Qty']);

        $top = $this->asAdmin()->getJson(route('admin.dashboard.get-top-products-report'))->json();
        $this->assertLessThanOrEqual(5, count($top));
        $this->assertSame(['Name', 'Qty', 'Percentage'], array_keys($top[0]));

        $retention = $this->asAdmin()->getJson(route('admin.dashboard.get-customer-retention'))->json();
        $this->assertSame(Customer::where('RoleID', 3)->count(), array_sum(array_column($retention, 'value')));

        $topCustomers = $this->asAdmin()->getJson(route('admin.dashboard.get-top10-spending-customers'))->json();
        $this->assertLessThanOrEqual(10, count($topCustomers));
        $this->assertGreaterThanOrEqual($topCustomers[1]['TotalSpent'], $topCustomers[0]['TotalSpent']);

        $this->asAdmin()->getJson(route('admin.dashboard.customers-by-day', $range))->assertOk();
        $this->asAdmin()->getJson(route('admin.dashboard.top-products'))->assertOk()->assertJsonStructure([['Name', 'Qty', 'Revenue']]);
        $this->asAdmin()->getJson(route('admin.dashboard.top-customers'))->assertOk()->assertJsonStructure([['Name', 'Total']]);
        $this->asAdmin()->getJson(route('admin.dashboard.get-customer-spending-by-day'))->assertOk();
    }

    public function test_json_endpoints_require_admin(): void
    {
        $this->getJson(route('admin.dashboard.revenue-by-day'))->assertUnauthorized();
    }

    // ===== Danh mục =====

    public function test_category_index_and_search(): void
    {
        $response = $this->asAdmin()->get('/Admin/Category/Index')->assertOk()->assertSee('Quản lý Danh mục');
        $this->assertCount(Category::count(), $response->viewData('categories'));

        $search = $this->asAdmin()->get('/Admin/Category/Index?search=' . urlencode('jean'))->assertOk();
        $this->assertNotEmpty($search->viewData('categories'));
        foreach ($search->viewData('categories') as $category) {
            $this->assertStringContainsStringIgnoringCase('jean', $category->CategoryName);
        }
    }

    public function test_category_create_update_delete(): void
    {
        $this->asAdmin()->post('/Admin/Category/Create', ['CategoryName' => '  Áo len  ', 'GroupID' => 1])
            ->assertRedirect(route('admin.category.index'))
            ->assertSessionHas('Success', 'Thêm danh mục thành công.');
        $category = Category::where('CategoryName', 'Áo len')->where('GroupID', 1)->firstOrFail();

        $this->asAdmin()->post('/Admin/Category/Create', ['CategoryName' => 'Áo len', 'GroupID' => 1])
            ->assertSessionHas('Error', 'Danh mục này đã tồn tại trong nhóm đã chọn.');
        $this->asAdmin()->post('/Admin/Category/Create', ['CategoryName' => '', 'GroupID' => 1])
            ->assertSessionHas('Error', 'Tên danh mục không được để trống.');
        $this->asAdmin()->post('/Admin/Category/Create', ['CategoryName' => 'Mới', 'GroupID' => 999])
            ->assertSessionHas('Error', 'Nhóm danh mục không hợp lệ.');

        $this->asAdmin()->post('/Admin/Category/Update', ['CategoryID' => $category->CategoryID, 'CategoryName' => 'Áo len cao cấp', 'GroupID' => 2])
            ->assertSessionHas('Success', 'Cập nhật danh mục thành công.');
        $this->assertSame('Áo len cao cấp', $category->fresh()->CategoryName);
        $this->assertSame(2, (int) $category->fresh()->GroupID);

        $this->asAdmin()->post('/Admin/Category/Update', ['CategoryID' => 999999, 'CategoryName' => 'X', 'GroupID' => 1])
            ->assertSessionHas('Error');

        $withProducts = Category::has('products')->first();
        $this->asAdmin()->post('/Admin/Category/Delete', ['id' => $withProducts->CategoryID])
            ->assertSessionHas('Error', 'Không thể xóa danh mục vì có sản phẩm liên kết.');
        $this->assertNotNull($withProducts->fresh());

        $this->asAdmin()->post('/Admin/Category/Delete', ['id' => $category->CategoryID])
            ->assertSessionHas('Success', 'Xóa danh mục thành công.');
        $this->assertNull($category->fresh());

        $this->asAdmin()->get('/Admin/Category/Delete/1')->assertNotFound();
    }

    // ===== Người dùng =====

    public function test_user_index_and_filters(): void
    {
        $response = $this->asAdmin()->get('/Admin/User/Index')->assertOk()->assertSee('Quản lý Người dùng');
        $this->assertCount(Customer::count(), $response->viewData('users'));
        $response->assertSee('Nguyễn Văn A');

        $byKeyword = $this->asAdmin()->get('/Admin/User/getAllUser?keyword=nguyenvana')->assertOk();
        $this->assertSame(['nguyenvana'], $byKeyword->viewData('users')->pluck('UserName')->all());

        $byRole = $this->asAdmin()->get('/Admin/User/getAllUser?roleId=1')->assertOk();
        $this->assertSame([Role::ADMIN], $byRole->viewData('users')->pluck('RoleID')->unique()->values()->all());

        Customer::where('UserName', 'tranthib')->update(['IsActive' => false]);
        $locked = $this->asAdmin()->get('/Admin/User/getAllUser?isActive=false')->assertOk()->assertDontSee('<html', false);
        $this->assertContains('tranthib', $locked->viewData('users')->pluck('UserName')->all());
        $this->assertNotContains(true, $locked->viewData('users')->pluck('IsActive')->all());
    }

    public function test_user_row_shows_role_select_and_lock_button_without_javascript(): void
    {
        $user = Customer::where('UserName', 'nguyenvana')->first();
        $page = $this->asAdmin()->get('/Admin/User/Index')->assertOk();

        // Ô chọn vai trò hiện sẵn, đủ 3 vai trò, đang chọn đúng vai trò hiện tại
        $page->assertSee(route('admin.user.change-role'), false)
            ->assertSee('<option value="' . Role::CUSTOMER . '" selected>Khách hàng</option>', false)
            ->assertSee('>Nhân viên</option>', false)
            ->assertSee('>Admin</option>', false)
            ->assertSee(route('admin.user.update-state'), false)
            ->assertSee('Khoá</button>', false);

        // Đổi vai trò bằng form thường (không dùng JavaScript)
        $this->asAdmin()->post('/Admin/User/ChangeRole', ['CustomerID' => $user->CustomerID, 'RoleID' => Role::STAFF])
            ->assertSessionHas('Success', 'Đổi vai trò thành công');
        $this->assertSame(Role::STAFF, $user->fresh()->RoleID);

        $this->asAdmin()->get('/Admin/User/Index')
            ->assertSee('<option value="' . Role::STAFF . '" selected>Nhân viên</option>', false);

        // Tài khoản đang đăng nhập không có ô đổi vai trò và nút khoá
        $this->asAdmin()->get('/Admin/User/getAllUser?keyword=admin')->assertOk()
            ->assertSee('Không thể tự đổi vai trò')
            ->assertSee('Hoạt động (bạn)');
    }

    public function test_user_state_and_role_changes(): void
    {
        $user = Customer::where('UserName', 'nguyenvana')->first();

        $this->asAdmin()->post('/Admin/User/UpdateState', ['CustomerID' => $user->CustomerID])
            ->assertRedirect(route('admin.user.index'))
            ->assertSessionHas('Success', 'Đã khoá tài khoản nguyenvana.');
        $this->assertFalse($user->fresh()->IsActive);

        $this->asAdmin()->post('/Admin/User/UpdateState', ['CustomerID' => $user->CustomerID]);
        $this->assertTrue($user->fresh()->IsActive);

        $this->asAdmin()->post('/Admin/User/ChangeRole', ['CustomerID' => $user->CustomerID, 'RoleID' => Role::STAFF])
            ->assertSessionHas('Success', 'Đổi vai trò thành công');
        $this->assertSame(Role::STAFF, $user->fresh()->RoleID);

        $this->asAdmin()->post('/Admin/User/ChangeRole', ['CustomerID' => $user->CustomerID, 'RoleID' => 999])
            ->assertSessionHas('Error');

        // Không tự khoá / tự hạ quyền chính mình
        $this->asAdmin()->post('/Admin/User/UpdateState', ['CustomerID' => $this->admin->CustomerID])
            ->assertSessionHas('Error', 'Bạn không thể khoá tài khoản của chính mình.');
        $this->asAdmin()->post('/Admin/User/ChangeRole', ['CustomerID' => $this->admin->CustomerID, 'RoleID' => Role::CUSTOMER])
            ->assertSessionHas('Error', 'Bạn không thể tự đổi vai trò của chính mình.');
        $this->assertTrue($this->admin->fresh()->IsActive);
        $this->assertSame(Role::ADMIN, $this->admin->fresh()->RoleID);

        // GET không còn đổi được trạng thái (bản gốc dùng link GET)
        $this->asAdmin()->get('/Admin/User/UpdateState?CustomerID=' . $user->CustomerID)->assertNotFound();
        $this->assertTrue($user->fresh()->IsActive);
    }

    public function test_add_and_drop_role(): void
    {
        $this->asAdmin()->post('/Admin/User/addRole', ['roleName' => 'Kho hàng'])
            ->assertSessionHas('Success', 'Thêm vai trò thành công!');
        $role = Role::where('RoleName', 'Kho hàng')->firstOrFail();

        $this->asAdmin()->post('/Admin/User/addRole', ['roleName' => 'Kho hàng'])->assertSessionHas('Error', 'Vai trò đã tồn tại!');
        $this->asAdmin()->post('/Admin/User/addRole', ['roleName' => '   '])->assertSessionHas('Error');

        $this->asAdmin()->post('/Admin/User/dropRole', ['roleId' => Role::CUSTOMER])
            ->assertSessionHas('Error', 'Không thể xoá vai trò mặc định của hệ thống!');

        $user = Customer::where('UserName', 'nguyenvana')->first();
        $user->update(['RoleID' => $role->RoleID]);
        $this->asAdmin()->post('/Admin/User/dropRole', ['roleId' => $role->RoleID])
            ->assertSessionHas('Error', 'Tồn tại người dùng thuộc vai trò này. Không thể xoá!');

        $user->update(['RoleID' => Role::CUSTOMER]);
        $this->asAdmin()->post('/Admin/User/dropRole', ['roleId' => $role->RoleID])
            ->assertSessionHas('Success', 'Xoá vai trò thành công!');
        $this->assertNull(Role::find($role->RoleID));
    }

    public function test_locked_user_is_logged_out_on_next_request(): void
    {
        $user = Customer::where('UserName', 'nguyenvana')->first();
        $this->asAdmin()->post('/Admin/User/UpdateState', ['CustomerID' => $user->CustomerID]);

        $this->flushSession();
        $this->withSession(['CustomerID' => $user->CustomerID])->get('/Order/Index')->assertRedirect(route('account.login'));
    }
}
