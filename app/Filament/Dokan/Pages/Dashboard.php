<?php

namespace App\Filament\Dokan\Pages;

use App\Filament\Dokan\Widgets\RecentOrders;
use App\Filament\Dokan\Widgets\VendorStatsOverview;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected string $view = 'filament.dokan.pages.dashboard';

    protected static ?string $title = 'Vendor Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon =
        \Filament\Support\Icons\Heroicon::OutlinedHome;

    protected function getHeaderWidgets(): array
    {
        return [
            VendorStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            RecentOrders::class,
        ];
    }
}