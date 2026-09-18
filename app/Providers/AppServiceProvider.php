<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\ServiceProvider;

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
        //
    }
}