<?php

namespace App\Filament\Dokan\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class VendorStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $dokan = Auth::guard('dokan')->user();

        if (!$dokan) {
            return [];
        }

        $products = Product::where('dokan_id', $dokan->id)->count();

        $orders = Order::where('dokan_id', $dokan->id)->count();

        $sales = Order::where('dokan_id', $dokan->id)
            ->sum('total_amount');

        $pendingOrders = Order::where('dokan_id', $dokan->id)
            ->where('order_status', 'pending')
            ->count();

        return [
            Stat::make('Total Products', $products)
                ->description('Products in your store')
                ->descriptionIcon(Heroicon::OutlinedRectangleStack),

            Stat::make('Total Orders', $orders)
                ->description('Orders from your store')
                ->descriptionIcon(Heroicon::OutlinedShoppingBag),

            Stat::make('Total Sales', 'Rs. ' . number_format($sales, 2))
                ->description('Total order amount')
                ->descriptionIcon(Heroicon::OutlinedBanknotes),

            Stat::make('Pending Orders', $pendingOrders)
                ->description('Orders waiting for processing')
                ->descriptionIcon(Heroicon::OutlinedClock),
        ];
    }
}