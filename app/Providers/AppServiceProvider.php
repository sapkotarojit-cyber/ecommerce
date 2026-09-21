<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

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
}
}