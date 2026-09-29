<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
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
                    ->state(function (Order $record) {

                        $orderItem = $record->orderItems->first();

                        if (! $orderItem) {
                            return null;
                        }

                        $variant = $orderItem->varient;

                        if (! $variant || empty($variant->images)) {
                            return null;
                        }

                        $images = $variant->images;

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

                // Order Status
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable(),

                // Payment Receipt
                TextColumn::make('payment_receipt')
                    ->label('Receipt')
                    ->formatStateUsing(
                        fn ($state) => $state
                            ? 'Uploaded'
                            : 'Not Uploaded'
                    )
                    ->badge()
                    ->color(
                        fn ($state) => $state
                            ? 'success'
                            : 'gray'
                    ),

                // Payment Method
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge(),

                // Payment Status
                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    })
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

                /*
                 * VIEW PAYMENT RECEIPT
                 */
                Action::make('viewReceipt')
                    ->label('Receipt')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->visible(fn (Order $record) =>
                        filled($record->payment_receipt)
                    )
                    ->url(fn (Order $record) =>
                        asset('storage/' . $record->payment_receipt)
                    )
                    ->openUrlInNewTab(),

                /*
                 * APPROVE PAYMENT
                 */
                Action::make('approvePayment')
                    ->label('Approve Payment')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Payment')
                    ->modalDescription(
                        'Are you sure you want to approve this payment? The order will be marked as paid and confirmed.'
                    )
                    ->visible(fn (Order $record) =>
                        $record->payment_status === 'pending'
                        && filled($record->payment_receipt)
                    )
                    ->action(function (Order $record) {

                        $record->update([
                            'payment_status' => 'paid',
                            'order_status' => 'confirmed',
                        ]);
                    })
                    ->successNotificationTitle(
                        'Payment approved successfully'
                    ),

                /*
                 * REJECT PAYMENT
                 */
                Action::make('rejectPayment')
                    ->label('Reject Payment')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Payment')
                    ->modalDescription(
                        'Are you sure you want to reject this payment?'
                    )
                    ->visible(fn (Order $record) =>
                        $record->payment_status === 'pending'
                    )
                    ->action(function (Order $record) {

                        $record->update([
                            'payment_status' => 'failed',
                        ]);
                    })
                    ->successNotificationTitle(
                        'Payment rejected'
                    ),

                /*
                 * NORMAL EDIT
                 */
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}