<?php

namespace Tests\Feature;

use App\Helpers\PasswordHasher;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomerProfile;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function customer(): Customer
    {
        return Customer::where('UserName', 'nguyenvana')->first();
    }

    // ===== Đăng nhập =====

    public function test_login_page(): void
    {
        $this->get('/Account/Login')->assertOk()
            ->assertSee('Đăng nhập')
            ->assertSee(route('forgot.index'), false);
    }

    public function test_customer_login_with_username_or_email(): void
    {
        $this->post('/Account/Login', ['UserName' => 'nguyenvana', 'Password' => '123456'])
            ->assertRedirect(route('product.index'))
            ->assertSessionHas('Success', 'Đăng nhập thành công')
            ->assertSessionHas('CustomerID', $this->customer()->CustomerID);

        $this->get('/Account/Logout')->assertRedirect(route('account.login'));
        $this->assertNull(session('CustomerID'));

        $this->post('/Account/Login', ['UserName' => $this->customer()->Email, 'Password' => '123456'])
            ->assertRedirect(route('product.index'));
    }

    public function test_admin_login_redirects_to_dashboard(): void
    {
        $this->post('/Account/Login', ['UserName' => 'admin', 'Password' => '123456'])
            ->assertRedirect(route('admin.dashboard.index'));
    }

    public function test_login_failures(): void
    {
        $this->from('/Account/Login')
            ->post('/Account/Login', ['UserName' => 'nguyenvana', 'Password' => 'sai'])
            ->assertRedirect('/Account/Login')
            ->assertSessionHasErrors(['UserName' => 'Tên đăng nhập hoặc mật khẩu không đúng']);

        $this->customer()->update(['IsActive' => false]);
        $this->post('/Account/Login', ['UserName' => 'nguyenvana', 'Password' => '123456'])
            ->assertSessionHasErrors(['UserName' => 'Tài khoản đã bị khoá']);
        $this->assertNull(session('CustomerID'));

        $this->post('/Account/Login', [])->assertSessionHasErrors(['UserName', 'Password']);
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        RateLimiter::clear('nguyenvana|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/Account/Login', ['UserName' => 'nguyenvana', 'Password' => 'sai']);
        }

        $this->post('/Account/Login', ['UserName' => 'nguyenvana', 'Password' => '123456'])
            ->assertSessionHasErrors('UserName');
        $this->assertNull(session('CustomerID'));
    }

    public function test_logged_in_user_visiting_login_is_redirected_home(): void
    {
        $this->withSession(['CustomerID' => $this->customer()->CustomerID])
            ->get('/Account/Login')
            ->assertRedirect(route('home.index'));
    }

    public function test_login_merges_guest_cart_into_customer_cart(): void
    {
        $customer = $this->customer();
        [$v1, $v2] = ProductVariant::orderBy('VariantID')->take(2)->pluck('VariantID')->all();

        CartItem::whereIn('CartID', Cart::where('CustomerID', $customer->CustomerID)->pluck('CartID'))->delete();
        $userCart = Cart::firstOrCreate(['CustomerID' => $customer->CustomerID], ['CartToken' => (string) Str::uuid()]);
        CartItem::create(['CartID' => $userCart->CartID, 'VariantID' => $v1, 'Quantity' => 1]);

        $guestCart = Cart::create(['CustomerID' => null, 'CartToken' => 'guest-token-123']);
        CartItem::create(['CartID' => $guestCart->CartID, 'VariantID' => $v1, 'Quantity' => 2]);
        CartItem::create(['CartID' => $guestCart->CartID, 'VariantID' => $v2, 'Quantity' => 3]);

        $response = $this->withCookie(CartService::COOKIE, 'guest-token-123')
            ->post('/Account/Login', ['UserName' => 'nguyenvana', 'Password' => '123456'])
            ->assertRedirect(route('product.index'));

        $this->assertSame(3, CartItem::where('CartID', $userCart->CartID)->where('VariantID', $v1)->value('Quantity'));
        $this->assertSame(3, CartItem::where('CartID', $userCart->CartID)->where('VariantID', $v2)->value('Quantity'));
        $this->assertDatabaseMissing('Carts', ['CartID' => $guestCart->CartID]);
        $this->assertCount(2, session('Cart'));

        $cookie = collect($response->headers->getCookies())->firstWhere(fn ($c) => $c->getName() === CartService::COOKIE);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isCleared());
    }

    // ===== Đăng ký =====

    public function test_register_creates_customer_with_default_profile(): void
    {
        $this->post('/Account/Register', [
            'UserName' => 'khachmoi',
            'Email' => 'khachmoi@example.com',
            'Password' => 'matkhau1',
        ])->assertRedirect(route('account.login'))
            ->assertSessionHas('Success', 'Đăng ký tài khoản thành công.');

        $customer = Customer::with('customerProfile')->where('UserName', 'khachmoi')->first();
        $this->assertSame(3, $customer->RoleID);
        $this->assertTrue($customer->IsActive);
        $this->assertTrue(PasswordHasher::verifyPassword('matkhau1', $customer->PasswordHash));
        $this->assertSame('Hồ Chí Minh', $customer->customerProfile->City);
        $this->assertSame('Quận Tân Phú', $customer->customerProfile->District);

        $this->post('/Account/Login', ['UserName' => 'khachmoi', 'Password' => 'matkhau1'])
            ->assertRedirect(route('product.index'));
    }

    public function test_register_validation(): void
    {
        $existing = $this->customer();

        $this->post('/Account/Register', ['UserName' => $existing->UserName, 'Email' => 'moi@example.com', 'Password' => '123456'])
            ->assertSessionHasErrors(['UserName' => 'Username đã tồn tại']);

        $this->post('/Account/Register', ['UserName' => 'userkhac', 'Email' => strtoupper($existing->Email), 'Password' => '123456'])
            ->assertSessionHasErrors(['Email' => 'Email đã tồn tại']);

        $this->post('/Account/Register', ['UserName' => 'có dấu cách', 'Email' => 'khong-phai-email', 'Password' => '123'])
            ->assertSessionHasErrors(['UserName', 'Email', 'Password']);

        $this->assertDatabaseMissing('Customers', ['UserName' => 'userkhac']);
    }

    // ===== Thông tin cá nhân =====

    public function test_profile_requires_login(): void
    {
        $this->get('/Account/Index')->assertRedirect(route('account.login'));
        $this->post('/Account/Update', ['FullName' => 'X'])->assertRedirect(route('account.login'));
    }

    public function test_profile_page_shows_current_data(): void
    {
        $customer = $this->customer();

        $this->withSession(['CustomerID' => $customer->CustomerID])
            ->get('/Account/Index')
            ->assertOk()
            ->assertSee('Thông tin cá nhân')
            ->assertSee($customer->customerProfile->FullName)
            ->assertSee('value="' . e($customer->customerProfile->City) . '" selected', false);
    }

    public function test_profile_update(): void
    {
        $customer = $this->customer();
        $other = Customer::where('UserName', '!=', 'nguyenvana')->where('RoleID', 3)->first();
        $otherName = $other->customerProfile->FullName;

        $this->withSession(['CustomerID' => $customer->CustomerID])
            ->post('/Account/Update', [
                'ProfileID' => $other->CustomerID, // cố sửa hồ sơ người khác: phải bị bỏ qua
                'FullName' => '  Nguyễn Văn Mới ',
                'PhoneNumber' => '090 111-2233',
                'DateOfBirth' => '2000-05-20',
                'Gender' => 'Nữ',
                'Address' => '12 Nguyễn Huệ',
                'City' => 'Thành phố Hà Nội',
                'District' => 'Quận Ba Đình',
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHas('Success', 'Cập nhật thông tin thành công!');

        $profile = CustomerProfile::find($customer->CustomerID);
        $this->assertSame('Nguyễn Văn Mới', $profile->FullName);
        $this->assertSame('0901112233', $profile->PhoneNumber);
        $this->assertSame('2000-05-20', $profile->DateOfBirth->format('Y-m-d'));
        $this->assertSame('Nữ', $profile->Gender);
        $this->assertSame('Quận Ba Đình', $profile->District);
        $this->assertSame($otherName, CustomerProfile::find($other->CustomerID)->FullName);
    }

    public function test_profile_update_validation(): void
    {
        $customer = $this->customer();

        $this->withSession(['CustomerID' => $customer->CustomerID])
            ->from('/Account/Index')
            ->post('/Account/Update', ['FullName' => 'A1', 'PhoneNumber' => '123', 'Gender' => 'X', 'DateOfBirth' => '2999-01-01'])
            ->assertRedirect('/Account/Index')
            ->assertSessionHasErrors(['FullName', 'PhoneNumber', 'Gender', 'DateOfBirth', 'City', 'District']);
    }

    public function test_profile_is_created_when_missing(): void
    {
        $customer = $this->customer();
        CustomerProfile::whereKey($customer->CustomerID)->delete();

        $this->withSession(['CustomerID' => $customer->CustomerID])->get('/Account/Index')->assertOk();

        $this->withSession(['CustomerID' => $customer->CustomerID])
            ->post('/Account/Update', ['FullName' => 'Trần Thị B', 'PhoneNumber' => '0912345678', 'City' => 'Hồ Chí Minh', 'District' => 'Quận 1'])
            ->assertSessionHas('Success');

        $this->assertSame('Trần Thị B', CustomerProfile::find($customer->CustomerID)->FullName);
    }

    // ===== Quên mật khẩu =====

    public function test_forgot_password_full_flow(): void
    {
        Mail::fake();
        $customer = $this->customer();

        $this->get('/ForgotPassword/Index')->assertOk()->assertSee('Quên mật khẩu');

        $this->postJson('/ForgotPassword/SendOTP', ['email' => ''])->assertJson(['success' => false, 'message' => 'Vui lòng nhập email']);
        $this->postJson('/ForgotPassword/SendOTP', ['email' => 'khongco@example.com'])->assertJson(['success' => false, 'message' => 'Email không tồn tại']);

        $this->postJson('/ForgotPassword/SendOTP', ['email' => $customer->Email])->assertJson(['success' => true]);
        $otp = session('OTP');
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);

        // Chưa xác thực OTP thì không được đổi mật khẩu
        $this->postJson('/ForgotPassword/ResetPassword', ['newPassword' => 'moi123456'])
            ->assertJson(['success' => false, 'message' => 'Phiên làm việc hết hạn']);

        $wrongOtp = $otp === '111111' ? '222222' : '111111';
        $this->postJson('/ForgotPassword/VerifyOTP', ['otp' => $wrongOtp])->assertJson(['success' => false, 'message' => 'OTP không đúng']);
        $this->postJson('/ForgotPassword/VerifyOTP', ['otp' => $otp])->assertJson(['success' => true]);

        $this->postJson('/ForgotPassword/ResetPassword', ['newPassword' => '123'])->assertJson(['success' => false]);
        $this->postJson('/ForgotPassword/ResetPassword', ['newPassword' => 'moi123456'])->assertJson(['success' => true]);

        $this->assertTrue(PasswordHasher::verifyPassword('moi123456', $customer->fresh()->PasswordHash));
        $this->assertNull(session('OTP'));

        // Dùng lại phiên cũ để đổi tiếp: bị từ chối
        $this->postJson('/ForgotPassword/ResetPassword', ['newPassword' => 'lanhai123'])->assertJson(['success' => false]);
    }

    public function test_forgot_password_otp_expiry_and_attempt_limit(): void
    {
        Mail::fake();
        $customer = $this->customer();

        $this->postJson('/ForgotPassword/VerifyOTP', ['otp' => '123456'])->assertJson(['message' => 'OTP không tồn tại']);

        $this->postJson('/ForgotPassword/SendOTP', ['email' => $customer->Email])->assertJson(['success' => true]);
        $wrongOtp = session('OTP') === '111111' ? '222222' : '111111';

        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/ForgotPassword/VerifyOTP', ['otp' => $wrongOtp])->assertJson(['message' => 'OTP không đúng']);
        }
        $this->postJson('/ForgotPassword/VerifyOTP', ['otp' => $wrongOtp])
            ->assertJson(['message' => 'Bạn đã nhập sai OTP quá nhiều lần. Vui lòng gửi lại mã mới.']);
        $this->assertNull(session('OTP'));

        $this->postJson('/ForgotPassword/SendOTP', ['email' => $customer->Email])->assertJson(['success' => true]);
        $otp = session('OTP');
        $this->travel(6)->minutes();
        $this->postJson('/ForgotPassword/VerifyOTP', ['otp' => $otp])->assertJson(['message' => 'OTP đã hết hạn']);
    }

    public function test_forgot_password_send_is_rate_limited(): void
    {
        Mail::fake();
        $email = $this->customer()->Email;

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/ForgotPassword/SendOTP', ['email' => $email])->assertJson(['success' => true]);
        }

        $this->postJson('/ForgotPassword/SendOTP', ['email' => $email])->assertJson(['success' => false]);
    }

    public function test_forgot_password_reports_mail_failure(): void
    {
        Mail::shouldReceive('raw')->andThrow(new \RuntimeException('SMTP down'));

        $this->postJson('/ForgotPassword/SendOTP', ['email' => $this->customer()->Email])
            ->assertJson(['success' => false, 'message' => 'Gửi email thất bại']);
    }
}
