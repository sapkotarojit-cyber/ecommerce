<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Observers\CatalogCacheObserver;
use App\Models\Dokan;
use App\Models\Category;
use App\Models\ProductVarient;
use App\Models\Product;
use App\Observers\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, fn () => new class implements LogoutResponseContract {
            public function toResponse($request): RedirectResponse
            {
                return redirect('/'); // Redirects to your web root upon logout
            }
        });
    }

    public function boot(): void
{


    View::composer('*', function ($view) {
        $cartCount = 0;
        if (Auth::check()) {
            $cartCount = Cart::where('user_id', Auth::id())->sum('qty');
        }
        $view->with('globalCartCount', $cartCount);
    });
    Order::observe(OrderObserver::class);

    Product::observe(CatalogCacheObserver::class);
    ProductVarient::observe(CatalogCacheObserver::class);
    Category::observe(CatalogCacheObserver::class);
    Dokan::observe(CatalogCacheObserver::class);
}
}