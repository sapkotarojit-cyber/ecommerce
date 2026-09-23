<?php

namespace App\Filament\Dokan\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class VendorMonthlySalesChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Sales';

    protected function getData(): array
    {
        $vendorId = Auth::id(); // Or Auth::user()->vendor_id depending on your relation
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $data[] = Order::where('dokan_id', $vendorId)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', date('Y'))
                ->sum('total_amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (NPR)',
                    'data' => $data,
                    'borderColor' => '#f59e0b', // Amber color matching your panel theme
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        // Change from 'Pie' to 'line' so 12 months display correctly
        return 'line';
    }
}
