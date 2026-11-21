<?php

namespace App\Http\Middleware;

use App\Helpers\CustomerSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Thay cho [CustomAuthorize]: chưa đăng nhập thì chuyển về /Account/Login. */
class CustomerAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! CustomerSession::check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
            }

            return redirect()->route('account.login');
        }

        return $next($request);
    }
}
