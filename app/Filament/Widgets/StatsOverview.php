<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Dokan;
use App\Models\ReturnRequest;
use App\Filament\Resources\ReturnRequests\ReturnRequestResource;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Revenue
        $totalRevenue = Order::where('payment_status', 'paid')
            ->sum('total_amount');

        // Return requests count
        $returnRequestsCount = ReturnRequest::where('status', 'requested')
            ->count();


            // Pending refunds count
        $pendingRefundsCount = ReturnRequest::whereIn('refund_status', [
            'pending',
            'processing',
        ])->count();

        // Completed refunds count
        $completedRefundsCount = ReturnRequest::where(
            'refund_status',
            'completed'
        )->count();

        // Total refunded amount
        $totalRefunded = ReturnRequest::where(
            'refund_status',
            'completed'
        )->sum('refund_amount');

        return [
            Stat::make(
                'Total Revenue',
                'Rs. ' . number_format($totalRevenue, 2)
            )
                ->description('Completed transactions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(
                'Pending Orders',
                Order::where('status', 'pending')->count()
            )
                ->description('Requires processing')
                ->color('warning'),

            Stat::make(
                'Pending Dokans',
                Dokan::where('status', Dokan::STATUS_PENDING)->count()
            )
                ->description('Awaiting approval')
                ->color('danger'),

            Stat::make(
                'Total Customers',
                User::count()
            )
                ->description('Registered accounts')
                ->color('info'),

            Stat::make(
                'Return Requests',
                $returnRequestsCount
            )
                ->description('Customers requesting returns')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning')
                ->url(ReturnRequestResource::getUrl('index')),

            Stat::make(
                'Pending Refunds',
                $pendingRefundsCount
            )
                ->description('Refunds requiring processing')
                ->color('danger')
                ->url(ReturnRequestResource::getUrl('index')),

            Stat::make(
                'Completed Refunds',
                $completedRefundsCount
            )
                ->description('Successfully refunded')
                ->color('success'),

            Stat::make(
                'Total Refunded',
                'Rs. ' . number_format($totalRefunded, 2)
            )
                ->description('Total amount refunded')
                ->color('info'),
        ];
    }
}