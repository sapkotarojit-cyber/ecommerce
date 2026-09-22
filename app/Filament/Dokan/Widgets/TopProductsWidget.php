<?php

namespace App\Filament\Dokan\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;

class TopProductsWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.dokan_id', $vendorId)
                    ->select('order_items.product_id', 'order_items.product_name')
                    ->selectRaw('SUM(order_items.qty) as total_sold')
                    ->selectRaw('SUM(order_items.total) as total_revenue')
                    ->groupBy('order_items.product_id', 'order_items.product_name')
                    ->orderByDesc('total_sold')
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product Name')
                    ->searchable(),

                TextColumn::make('total_sold')
                    ->label('Units Sold')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->money('NPR')
                    ->sortable(),
            ])
    

            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
