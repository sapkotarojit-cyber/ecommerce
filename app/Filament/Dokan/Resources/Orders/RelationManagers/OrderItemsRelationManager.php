<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderItem;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'order_items';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                ImageColumn::make('varient.images')
                    ->label('Item Image')
                    ->state(function (OrderItem $record) {
                        $variant = $record->varient;
                        if ($variant && !empty($variant->images)) {
                            return is_array($variant->images) ? $variant->images[0] : $variant->images;
                        }
                        return null;
                    })
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                TextColumn::make('varient.title')
                    ->label('Variant Title')
                    ->searchable(),

                // Price Column (with fallback to variant price)
                TextColumn::make('price')
                    ->label('Price')
                    ->state(function (OrderItem $record) {
                        return $record->price ?? $record->unit_price ?? $record->varient?->price ?? 0;
                    })
                    ->money('NPR')
                    ->sortable(),

                TextColumn::make('qty')
                    ->label('Quantity')
                    ->numeric(),

                // Total Column (Price x Quantity)
                TextColumn::make('total')
                    ->label('Total')
                    ->state(function (OrderItem $record) {
                        $price = $record->price ?? $record->unit_price ?? $record->varient?->price ?? 0;
                        $quantity = $record->qty ?? $record->quantity ?? 1;

                        return $price * $quantity;
                    })
                    ->money('NPR'),
            ]);
    }
}