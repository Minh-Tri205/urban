<?php

namespace App\Http\Middleware;

use App\Helpers\CustomerSession;
use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Chức năng chỉ dành cho Admin (RoleID 1), nhân viên không được dùng. Chạy sau middleware "admin". */
class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (CustomerSession::roleId() !== Role::ADMIN) {
            $message = 'Chỉ quản trị viên mới được dùng chức năng này.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : redirect()->route('admin.dashboard.index')->with('Error', $message);
        }

        return $next($request);
    }
}
