<?php

namespace App\Http\Middleware;

use App\Helpers\CustomerSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chỉ Admin (RoleID 1) và Nhân viên (RoleID 2) được vào /Admin.
 * Bản C# chưa kiểm tra quyền ở khu Admin, đây là phần bổ sung.
 */
class AdminAuthorize
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! CustomerSession::check()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401)
                : redirect()->route('account.login');
        }

        if (! CustomerSession::isAdmin()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Bạn không có quyền truy cập.'], 403)
                : redirect()->route('home.index')->with('Error', 'Bạn không có quyền truy cập trang quản lý.');
        }

        return $next($request);
    }
}
