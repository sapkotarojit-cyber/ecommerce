<?php

namespace App\Providers\Filament;

use App\Filament\Dokan\Pages\Dashboard;
use App\Filament\Dokan\Resources\Products\ProductResource;
use App\Filament\Dokan\Widgets\VendorStatsOverview;
use App\Models\Dokan;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook; 
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DokanPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dokan')
            ->path('vendor')
            ->login()
            ->homeUrl('/')
            ->brandName(function () {
                $user = Auth::guard('dokan')->user() 
                    ?? Auth::guard('web')->user() 
                    ?? Auth::user();

                if (! $user) {
                    return 'Vendor Panel';
                }

                return $user->name 
                    ?? $user->store_name 
                    ?? $user->dokan?->name 
                    ?? $user->dokan?->store_name 
                    ?? 'Vendor';
            })
            ->registration()
            ->authGuard('dokan')
            ->authPasswordBroker('dokans')
            ->passwordReset()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->resources([
                ProductResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Dokan/Resources'), for: 'App\Filament\Dokan\Resources')
            ->discoverPages(in: app_path('Filament/Dokan/Pages'), for: 'App\Filament\Dokan\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Dokan/Widgets'), for: 'App\Filament\Dokan\Widgets')
            ->widgets([
                VendorStatsOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}