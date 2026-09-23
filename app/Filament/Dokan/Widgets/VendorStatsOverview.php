<?php

namespace App\Filament\Dokan\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Dokan;
use App\Models\ReturnRequest;
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

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products = Product::where('dokan_id', $dokan->id)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        $orders = Order::where('dokan_id', $dokan->id)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Total Sales
        |--------------------------------------------------------------------------
        */

        $sales = Order::where('dokan_id', $dokan->id)
            ->where('order_status', 'completed')
            ->sum('total_amount');

        /*
        |--------------------------------------------------------------------------
        | Pending Orders
        |--------------------------------------------------------------------------
        */

        $pendingOrders = Order::where('dokan_id', $dokan->id)
            ->where('order_status', 'pending')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Return Requests
        |--------------------------------------------------------------------------
        */

        $returnRequests = ReturnRequest::where('dokan_id', $dokan->id)
            ->where('status', 'requested')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Pending Refunds
        |--------------------------------------------------------------------------
        */

        $pendingRefunds = ReturnRequest::where('dokan_id', $dokan->id)
            ->whereIn('refund_status', ['pending', 'processing'])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Completed Refunds
        |--------------------------------------------------------------------------
        */

        $completedRefunds = ReturnRequest::where('dokan_id', $dokan->id)
            ->where('refund_status', 'completed')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Total Refunded Amount
        |--------------------------------------------------------------------------
        */

        $totalRefund = ReturnRequest::where('dokan_id', $dokan->id)
            ->where('refund_status', 'completed')
            ->sum('refund_amount');

       

       

        /*
        |--------------------------------------------------------------------------
        | Stats
        |--------------------------------------------------------------------------
        */

        return [

            Stat::make('Total Products', $products)
                ->description('Products in your store')
                ->descriptionIcon(Heroicon::OutlinedRectangleStack),

            Stat::make('Total Orders', $orders)
                ->description('Orders from your store')
                ->descriptionIcon(Heroicon::OutlinedShoppingBag),

            Stat::make(
                'Total Sales',
                'Rs. ' . number_format($sales, 2)
            )
                ->description('Completed order sales')
                ->descriptionIcon(Heroicon::OutlinedBanknotes),

            Stat::make('Pending Orders', $pendingOrders)
                ->description('Orders waiting for processing')
                ->descriptionIcon(Heroicon::OutlinedClock),

            Stat::make('Return Requests', $returnRequests)
                ->description('Customer return requests')
                ->descriptionIcon(Heroicon::OutlinedArrowPath)
                ->url(url('/vendor/return-requests')),

            Stat::make('Pending Refunds', $pendingRefunds)
                ->description('Refunds waiting for processing')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->url(url('/vendor/return-requests')),

            Stat::make('Completed Refunds', $completedRefunds)
                ->description('Refunds completed')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->url(url('/vendor/return-requests')),

            Stat::make(
                'Total Refund',
                'Rs. ' . number_format($totalRefund, 2)
            )
                ->description('Total amount refunded')
                ->descriptionIcon(Heroicon::OutlinedArrowUturnLeft),

            
        ];
    }
}