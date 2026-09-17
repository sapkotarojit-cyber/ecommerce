<?php

namespace App\Filament\Dokan\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class RecentOrders extends TableWidget
{
    protected static ?string $heading = 'Recent Orders';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        $dokan = Auth::guard('dokan')->user();

        return $table
            ->query(
                Order::query()
                    ->where('dokan_id', $dokan?->id)
                    ->with('user')
                    ->latest()
            )
            ->columns([
                TextColumn::make('tracking_number')
                    ->label('Order')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('NPR'),

                TextColumn::make('order_status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5]);
    }
}