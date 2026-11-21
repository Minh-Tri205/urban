<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

/**
 * ASP.NET MVC không phân biệt hoa thường trong URL và cho phép bỏ trống action (mặc định Index).
 * Laravel thì phân biệt, nên với request GET không khớp route nào, controller này tìm route
 * tương ứng rồi chuyển hướng 301 về URL chuẩn. Không tìm thấy thì trả về trang 404.
 */
class LegacyUrlController extends Controller
{
    public function fallback(Request $request, Router $router)
    {
        if ($request->isMethod('GET')) {
            $path = '/' . trim($request->path(), '/');
            $candidates = [$path, rtrim($path, '/') . '/Index', rtrim($path, '/') . '/Dashboard/Index'];

            foreach ($candidates as $candidate) {
                if ($url = $this->findCanonicalUrl($router, $candidate)) {
                    $query = $request->getQueryString();

                    return redirect($url . ($query ? '?' . $query : ''), 301);
                }
            }
        }

        abort(404);
    }

    private function findCanonicalUrl(Router $router, string $path): ?string
    {
        /** @var Route $route */
        foreach ($router->getRoutes()->get('GET') as $route) {
            if ($route->isFallback) {
                continue;
            }

            // Regex đã biên dịch của route, thêm cờ i để không phân biệt hoa thường
            $regex = $route->getCompiled()->getRegex();
            $regex = substr($regex, 0, strrpos($regex, '}') + 1) . 'i' . substr($regex, strrpos($regex, '}') + 1);

            if (preg_match($regex, rawurldecode($path), $matches)) {
                $params = array_filter($matches, fn ($key) => is_string($key), ARRAY_FILTER_USE_KEY);
                $uri = preg_replace_callback('/\{(\w+)\??\}/', fn ($m) => $params[$m[1]] ?? '', $route->uri());

                return url(rtrim($uri, '/'));
            }
        }

        return null;
    }
}
