<?php

namespace App\Http\Controllers;

use App\Helpers\CustomerSession;
use App\Helpers\PasswordHasher;
use App\Models\Customer;
use App\Models\CustomerProfile;
use App\Models\Role;
use App\Rules\ValidFullName;
use App\Rules\VietnamesePhone;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class AccountController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    public function index()
    {
        $customer = CustomerSession::user();
        $profile = $customer->customerProfile ?? new CustomerProfile(['ProfileID' => $customer->CustomerID]);

        return view('account.index', compact('profile'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'FullName' => ['required', 'string', new ValidFullName()],
            'PhoneNumber' => ['required', 'string', new VietnamesePhone()],
            'DateOfBirth' => ['nullable', 'date', 'before:today'],
            'Gender' => ['nullable', 'in:Nam,Nữ,Khác'],
            'Address' => ['nullable', 'string', 'max:255'],
            'City' => ['required', 'string', 'max:100'],
            'District' => ['required', 'string', 'max:100'],
        ], [
            'FullName.required' => 'Vui lòng nhập họ và tên.',
            'PhoneNumber.required' => 'Vui lòng nhập số điện thoại.',
            'DateOfBirth.date' => 'Ngày sinh không hợp lệ.',
            'DateOfBirth.before' => 'Ngày sinh phải trước ngày hôm nay.',
            'Gender.in' => 'Giới tính không hợp lệ.',
            'Address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'City.required' => 'Vui lòng chọn tỉnh / thành phố.',
            'District.required' => 'Vui lòng chọn quận / huyện.',
        ]);

        $data['FullName'] = trim($data['FullName']);
        $data['PhoneNumber'] = VietnamesePhone::normalize($data['PhoneNumber']);

        try {
            // Bản gốc lấy ProfileID từ ô ẩn trên form nên có thể sửa hồ sơ người khác; ở đây luôn dùng tài khoản đang đăng nhập
            CustomerProfile::updateOrCreate(['ProfileID' => CustomerSession::id()], $data);
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('Error', 'Lỗi khi cập nhật thông tin. Vui lòng thử lại.');
        }

        return redirect()->route('account.index')->with('Success', 'Cập nhật thông tin thành công!');
    }

    public function login()
    {
        if (CustomerSession::check()) {
            return redirect()->route('home.index');
        }

        return view('account.login');
    }

    public function loginPost(Request $request, CartService $cartService)
    {
        $request->validate([
            'UserName' => ['required', 'string', 'max:100'],
            'Password' => ['required', 'string'],
        ], [
            'UserName.required' => 'Vui lòng nhập tên đăng nhập hoặc email.',
            'Password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $userName = trim($request->input('UserName'));
        $throttleKey = Str::lower($userName) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('UserName'))
                ->withErrors(['UserName' => "Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau {$seconds} giây."]);
        }

        $customer = Customer::where('UserName', $userName)->orWhere('Email', $userName)->first();

        if (! $customer || ! PasswordHasher::verifyPassword($request->input('Password'), $customer->PasswordHash)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withInput($request->only('UserName'))
                ->withErrors(['UserName' => 'Tên đăng nhập hoặc mật khẩu không đúng']);
        }

        RateLimiter::clear($throttleKey);

        if ($customer->IsActive === false) {
            return back()->withInput($request->only('UserName'))
                ->withErrors(['UserName' => 'Tài khoản đã bị khoá']);
        }

        CustomerSession::login($customer);

        try {
            $cartService->mergeGuestCartIntoCustomer($customer->CustomerID);
        } catch (Throwable $e) {
            // Giống bản gốc: lỗi gộp giỏ hàng không làm gián đoạn đăng nhập
            report($e);
        }

        if ($customer->isAdmin()) {
            return redirect()->route('admin.dashboard.index');
        }

        return redirect()->route('product.index')->with('Success', 'Đăng nhập thành công');
    }

    public function register()
    {
        return view('account.register');
    }

    public function registerPost(Request $request)
    {
        $data = $request->validate([
            'UserName' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_.\-]+$/', 'unique:Customers,UserName'],
            'Email' => ['required', 'string', 'email', 'max:100', 'unique:Customers,Email'],
            'Password' => ['required', 'string', 'min:6', 'max:100'],
        ], [
            'UserName.required' => 'Vui lòng nhập tên đăng nhập.',
            'UserName.max' => 'Tên đăng nhập không được vượt quá 50 ký tự.',
            'UserName.regex' => 'Tên đăng nhập chỉ gồm chữ không dấu, số và các ký tự _ . -',
            'UserName.unique' => 'Username đã tồn tại',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Email không hợp lệ.',
            'Email.max' => 'Email không được vượt quá 100 ký tự.',
            'Email.unique' => 'Email đã tồn tại',
            'Password.required' => 'Vui lòng nhập mật khẩu.',
            'Password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'Password.max' => 'Mật khẩu không được vượt quá 100 ký tự.',
        ]);

        try {
            DB::transaction(function () use ($data) {
                $customer = Customer::create([
                    'UserName' => trim($data['UserName']),
                    'Email' => trim($data['Email']),
                    'PasswordHash' => PasswordHasher::hashPassword($data['Password']),
                    'CreatedAt' => now(),
                    'IsActive' => true,
                    'RoleID' => Role::CUSTOMER,
                ]);

                // Hồ sơ mặc định giống bản gốc
                CustomerProfile::create([
                    'ProfileID' => $customer->CustomerID,
                    'Address' => '140 Lê Trọng Tấn',
                    'City' => 'Hồ Chí Minh',
                    'District' => 'Quận Tân Phú',
                    'DateOfBirth' => '1900-01-01',
                    'Gender' => 'Nam',
                ]);
            });
        } catch (Throwable $e) {
            report($e);

            return back()->withInput($request->except('Password'))
                ->withErrors(['UserName' => 'Đăng ký thất bại. Vui lòng thử lại.']);
        }

        return redirect()->route('account.login')->with('Success', 'Đăng ký tài khoản thành công.');
    }

    public function logout(Request $request)
    {
        // Session.Abandon(): xóa toàn bộ session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login');
    }
}
