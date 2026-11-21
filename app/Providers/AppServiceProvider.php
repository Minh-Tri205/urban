<?php

namespace App\Providers;

use App\Helpers\CartSessionHelper;
use App\Helpers\CustomerSession;
use App\Models\CategoryGroup;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CartSessionHelper::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Layout khách hàng: menu danh mục (thay @Html.Action("DanhMuc", "Product")) và số dòng trong giỏ
        View::composer('layouts.app', function ($view) {
            $view->with([
                'menuCategoryGroups' => CategoryGroup::with('categories')->orderBy('GroupID')->get(),
                'cartCount' => app(CartSessionHelper::class)->count(),
                'currentCustomer' => CustomerSession::user(),
            ]);
        });
    }
}
