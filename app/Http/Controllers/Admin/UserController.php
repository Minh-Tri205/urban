<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomerSession;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Role;
use Illuminate\Http\Request;

/** Chỉ Admin (RoleID 1) dùng được, xem middleware admin.only trong routes/web.php. */
class UserController extends Controller
{
    /** Vai trò hệ thống: code phân quyền phụ thuộc vào các ID này nên không cho xóa. */
    private const SYSTEM_ROLES = [Role::ADMIN, Role::STAFF, Role::CUSTOMER];

    public function index(Request $request)
    {
        return view('admin.user.index', [
            'roles' => Role::orderBy('RoleID')->get(),
            'keyword' => (string) $request->query('keyword', ''),
            'users' => $this->queryUsers($request),
        ]);
    }

    /** Bảng người dùng (partial) cho bộ lọc AJAX. Thay cho stored procedure sp_getAllUser. */
    public function getAllUser(Request $request)
    {
        return view('admin.user.partials.user-table', [
            'roles' => Role::orderBy('RoleID')->get(),
            'users' => $this->queryUsers($request),
        ]);
    }

    public function updateState(Request $request)
    {
        $customer = Customer::find((int) $request->input('CustomerID'));

        if ($customer === null) {
            return redirect()->route('admin.user.index')->with('Error', 'Người dùng không tồn tại.');
        }

        if ($customer->CustomerID === CustomerSession::id()) {
            return redirect()->route('admin.user.index')->with('Error', 'Bạn không thể khoá tài khoản của chính mình.');
        }

        $customer->update(['IsActive' => ! $customer->IsActive]);

        return redirect()->route('admin.user.index')->with(
            'Success',
            $customer->IsActive ? "Đã mở khoá tài khoản {$customer->UserName}." : "Đã khoá tài khoản {$customer->UserName}."
        );
    }

    public function changeRole(Request $request)
    {
        $customer = Customer::find((int) $request->input('CustomerID'));
        $role = Role::find((int) $request->input('RoleID'));

        if ($customer === null || $role === null) {
            return redirect()->route('admin.user.index')->with('Error', 'Người dùng hoặc vai trò không hợp lệ.');
        }

        if ($customer->CustomerID === CustomerSession::id()) {
            return redirect()->route('admin.user.index')->with('Error', 'Bạn không thể tự đổi vai trò của chính mình.');
        }

        $customer->update(['RoleID' => $role->RoleID]);

        return redirect()->route('admin.user.index')->with('Success', 'Đổi vai trò thành công');
    }

    public function addRole(Request $request)
    {
        $roleName = trim((string) $request->input('roleName'));

        if ($roleName === '' || mb_strlen($roleName) > 50) {
            return redirect()->route('admin.user.index')->with('Error', 'Tên vai trò không hợp lệ (1-50 ký tự).');
        }

        if (Role::where('RoleName', $roleName)->exists()) {
            return redirect()->route('admin.user.index')->with('Error', 'Vai trò đã tồn tại!');
        }

        Role::create(['RoleName' => $roleName]);

        return redirect()->route('admin.user.index')->with('Success', 'Thêm vai trò thành công!');
    }

    public function dropRole(Request $request)
    {
        $role = Role::find((int) $request->input('roleId'));

        if ($role === null) {
            return redirect()->route('admin.user.index')->with('Error', 'Vai trò không hợp lệ!');
        }

        if (in_array($role->RoleID, self::SYSTEM_ROLES, true)) {
            return redirect()->route('admin.user.index')->with('Error', 'Không thể xoá vai trò mặc định của hệ thống!');
        }

        if (Customer::where('RoleID', $role->RoleID)->exists()) {
            return redirect()->route('admin.user.index')->with('Error', 'Tồn tại người dùng thuộc vai trò này. Không thể xoá!');
        }

        $role->delete();

        return redirect()->route('admin.user.index')->with('Success', 'Xoá vai trò thành công!');
    }

    private function queryUsers(Request $request)
    {
        $keyword = trim((string) $request->query('keyword', ''));
        $roleId = $request->query('roleId');
        $isActive = $request->query('isActive');

        return Customer::query()
            ->leftJoin('CustomerProfile as cp', 'cp.ProfileID', '=', 'Customers.CustomerID')
            ->leftJoin('Roles as r', 'r.RoleID', '=', 'Customers.RoleID')
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = '%' . addcslashes($keyword, '%_\\') . '%';
                $q->where(fn ($w) => $w->where('Customers.UserName', 'like', $like)
                    ->orWhere('Customers.Email', 'like', $like)
                    ->orWhere('cp.FullName', 'like', $like));
            })
            ->when(is_string($roleId) && ctype_digit($roleId), fn ($q) => $q->where('Customers.RoleID', (int) $roleId))
            ->when(in_array($isActive, ['true', 'false'], true), fn ($q) => $q->where('Customers.IsActive', $isActive === 'true'))
            ->orderBy('Customers.CustomerID')
            ->get([
                'Customers.CustomerID', 'Customers.UserName', 'Customers.Email', 'Customers.RoleID',
                'Customers.IsActive', 'Customers.CreatedAt', 'cp.FullName', 'cp.PhoneNumber', 'r.RoleName',
            ]);
    }
}
