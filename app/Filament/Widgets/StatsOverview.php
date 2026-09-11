<?php

namespace App\Filament\Widgets;

use App\Models\Dokan;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', '$' . number_format(Order::where('payment_status', 'paid')->sum('total_amount'), 2))
                ->description('Completed transactions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pending Orders', Order::where('status', 'pending')->count())
                ->description('Requires processing')
                ->color('warning'),

            Stat::make('Pending Dokans', Dokan::where('status', Dokan::STATUS_PENDING)->count())
                ->description('Awaiting approval')
                ->color('danger'),

            Stat::make('Total Customers', User::count())
                ->description('Registered accounts')
                ->color('info'),
        ];
    }
}