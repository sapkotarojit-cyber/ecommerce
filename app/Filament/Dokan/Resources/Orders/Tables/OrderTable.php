<?php

namespace App\Filament\Dokan\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('tracking_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('NPR')
                    ->sortable(),

                TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge(),

                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge(),

                TextColumn::make('shippingAddress.full_address')
                    ->label('Shipping Address')
                    ->limit(40),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y')
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}