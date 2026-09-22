<?php

namespace App\Filament\Dokan\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([


              // Product Image
                ImageColumn::make('product_image')
                    ->label('Image')
                    ->state(function ($record) {
                        $orderItem = $record->orderItems->first();

                        if (! $orderItem) {
                            return null;
                        }

                        $variant = $orderItem->varient;

                        if (! $variant || empty($variant->images)) {
                            return null;
                        }

                        $images = $variant->images;

                        // If images are stored as JSON/string
                        if (is_string($images)) {
                            $decoded = json_decode($images, true);

                            if (is_array($decoded)) {
                                $images = $decoded;
                            }
                        }

                        if (is_array($images)) {
                            return $images[0] ?? null;
                        }

                        return $images;
                    })
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                // Tracking Number
                TextColumn::make('tracking_number')
                    ->label('Tracking Number')
                    ->searchable()
                    ->sortable(),

                // Customer
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                // Vendor
                TextColumn::make('dokan.company_name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

                // Shipping Address
                TextColumn::make('shippingAddress.full_address')
                    ->label('Shipping Address')
                    ->wrap()
                    ->limit(60),

                // Total Amount
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->sortable(),

                // Status
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable(),

                // Payment Method
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge(),

                // Payment Status
                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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