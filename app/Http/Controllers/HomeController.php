<?php

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::with('productImages')
            ->where('IsActive', true)
            ->orderBy('ProductID')
            ->take(6)
            ->get();

        return view('home.index', compact('products'));
    }

    public function about()
    {
        return view('home.about');
    }

    /** Tương đương ErrorController.NotFound (Web.config chuyển lỗi 404 tới /Error/NotFound). */
    public function notFound()
    {
        return response()->view('errors.404', [], 404);
    }
}
