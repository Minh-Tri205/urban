<?php

namespace App\Http\Controllers;

use App\Helpers\PasswordHasher;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * Quên mật khẩu qua OTP gửi email. So với bản gốc có bổ sung:
 * - Phải xác thực OTP thành công mới được đặt lại mật khẩu (bản gốc bỏ qua bước này được)
 * - Nhập sai OTP quá 5 lần thì OTP bị hủy
 * - Giới hạn gửi OTP 3 lần / 10 phút cho mỗi email và IP
 */
class ForgotPasswordController extends Controller
{
    private const OTP_TTL_MINUTES = 5;
    private const MAX_VERIFY_ATTEMPTS = 5;
    private const SESSION_KEYS = ['OTP', 'OTP_EXPIRE', 'OTP_CUSTOMER_ID', 'OTP_ATTEMPTS', 'OTP_VERIFIED'];

    public function index()
    {
        return view('forgot-password.index');
    }

    public function sendOtp(Request $request)
    {
        $email = trim((string) $request->input('email'));

        if ($email === '') {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập email']);
        }

        $throttleKey = 'otp|' . mb_strtolower($email) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);

            return response()->json(['success' => false, 'message' => "Bạn đã yêu cầu OTP quá nhiều lần. Vui lòng thử lại sau {$minutes} phút."]);
        }

        $customer = Customer::where('Email', $email)->first();
        if ($customer === null) {
            return response()->json(['success' => false, 'message' => 'Email không tồn tại']);
        }

        RateLimiter::hit($throttleKey, 600);

        $otp = (string) random_int(100000, 999999);

        $request->session()->forget(self::SESSION_KEYS);
        $request->session()->put([
            'OTP' => $otp,
            'OTP_EXPIRE' => now()->addMinutes(self::OTP_TTL_MINUTES)->timestamp,
            'OTP_CUSTOMER_ID' => $customer->CustomerID,
            'OTP_ATTEMPTS' => 0,
        ]);

        if (! $this->sendOtpToEmail($customer->Email, $otp)) {
            return response()->json(['success' => false, 'message' => 'Gửi email thất bại']);
        }

        return response()->json(['success' => true]);
    }

    public function verifyOtp(Request $request)
    {
        $session = $request->session();

        if (! $session->has('OTP') || ! $session->has('OTP_EXPIRE')) {
            return response()->json(['success' => false, 'message' => 'OTP không tồn tại']);
        }

        if (now()->timestamp > $session->get('OTP_EXPIRE')) {
            $session->forget(self::SESSION_KEYS);

            return response()->json(['success' => false, 'message' => 'OTP đã hết hạn']);
        }

        if (! hash_equals((string) $session->get('OTP'), trim((string) $request->input('otp')))) {
            $attempts = $session->increment('OTP_ATTEMPTS');

            if ($attempts >= self::MAX_VERIFY_ATTEMPTS) {
                $session->forget(self::SESSION_KEYS);

                return response()->json(['success' => false, 'message' => 'Bạn đã nhập sai OTP quá nhiều lần. Vui lòng gửi lại mã mới.']);
            }

            return response()->json(['success' => false, 'message' => 'OTP không đúng']);
        }

        $session->put('OTP_VERIFIED', true);

        return response()->json(['success' => true]);
    }

    public function resetPassword(Request $request)
    {
        $newPassword = (string) $request->input('newPassword');
        $session = $request->session();

        if (trim($newPassword) === '' || mb_strlen($newPassword) < 6 || mb_strlen($newPassword) > 100) {
            return response()->json(['success' => false, 'message' => 'Mật khẩu không hợp lệ (6-100 ký tự)']);
        }

        if (! $session->has('OTP_CUSTOMER_ID') || $session->get('OTP_VERIFIED') !== true
            || now()->timestamp > (int) $session->get('OTP_EXPIRE')) {
            return response()->json(['success' => false, 'message' => 'Phiên làm việc hết hạn']);
        }

        $customer = Customer::find($session->get('OTP_CUSTOMER_ID'));
        if ($customer === null) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tài khoản']);
        }

        $customer->update(['PasswordHash' => PasswordHasher::hashPassword($newPassword)]);
        $session->forget(self::SESSION_KEYS);

        return response()->json(['success' => true]);
    }

    private function sendOtpToEmail(string $email, string $otp): bool
    {
        $body = "Xin chào,\n\n"
            . "Mã OTP của bạn là: {$otp}\n\n"
            . '⏰ Hiệu lực trong ' . self::OTP_TTL_MINUTES . " phút.\n"
            . "Nếu bạn không yêu cầu, hãy bỏ qua email này.\n\n"
            . 'FashionStore';

        try {
            Mail::raw($body, fn ($message) => $message->to($email)->subject('Mã OTP đặt lại mật khẩu'));

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
