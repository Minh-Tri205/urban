@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('styles')
    <style>
        .forgot-card {
            max-width: 420px;
            margin: 60px auto;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
        }

        .step-title {
            font-size: 14px;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="card forgot-card">
        <div class="card-body p-4">
            <h4 class="text-center mb-4">🔐 Quên mật khẩu</h4>

            <!-- STEP 1 -->
            <div id="step-phone">
                <div class="step-title">Bước 1: Nhập email</div>
                <div class="form-group mb-3">
                    <input type="email"
                           id="email"
                           class="form-control"
                           placeholder="Email đã đăng ký"/>

                </div>
                <button class="btn btn-primary w-100" id="btn-send-otp" onclick="sendOTP()">
                    Gửi mã OTP
                </button>
            </div>

            <!-- STEP 2 -->
            <div id="step-otp" style="display:none">
                <div class="step-title">Bước 2: Nhập mã OTP</div>
                <div class="form-group mb-3">
                    <input type="text"
                           id="otp"
                           class="form-control text-center"
                           placeholder="Nhập mã OTP"
                           inputmode="numeric"
                           maxlength="6"/>
                </div>
                <button class="btn btn-success w-100" onclick="verifyOTP()">
                    Xác nhận OTP
                </button>
            </div>

            <!-- STEP 3 -->
            <div id="step-reset" style="display:none">
                <div class="step-title">Bước 3: Đặt mật khẩu mới</div>
                <div class="form-group mb-3">
                    <input type="password"
                           id="newPassword"
                           class="form-control"
                           placeholder="Mật khẩu mới (ít nhất 6 ký tự)"
                           autocomplete="new-password"/>
                </div>
                <button class="btn btn-danger w-100" onclick="resetPassword()">
                    Đổi mật khẩu
                </button>
            </div>

            <hr/>

            <div class="text-center">
                <a href="{{ route('account.login') }}">← Quay lại đăng nhập</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function showError() {
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
        }

        function sendOTP() {
            const $btn = $('#btn-send-otp').prop('disabled', true);

            $.post(@json(route('forgot.send-otp')), {
                email: $('#email').val()
            }, function (res) {
                if (res.success) {
                    alert('OTP đã được gửi qua email');
                    $('#step-phone').hide();
                    $('#step-otp').fadeIn();
                } else {
                    alert(res.message);
                }
            }).fail(showError).always(function () {
                $btn.prop('disabled', false);
            });
        }

        function verifyOTP() {
            $.post(@json(route('forgot.verify-otp')), {
                otp: $('#otp').val()
            }, function (res) {
                if (res.success) {
                    alert('OTP hợp lệ');
                    $('#step-otp').hide();
                    $('#step-reset').fadeIn();
                } else {
                    alert(res.message);
                    if (res.message.indexOf('gửi lại') !== -1 || res.message.indexOf('hết hạn') !== -1 || res.message.indexOf('không tồn tại') !== -1) {
                        $('#otp').val('');
                        $('#step-otp').hide();
                        $('#step-phone').fadeIn();
                    }
                }
            }).fail(showError);
        }

        function resetPassword() {
            $.post(@json(route('forgot.reset')), {
                newPassword: $('#newPassword').val()
            }, function (res) {
                if (res.success) {
                    alert('Đổi mật khẩu thành công');
                    window.location.href = @json(route('account.login'));
                } else {
                    alert(res.message);
                }
            }).fail(showError);
        }
    </script>
@endsection
