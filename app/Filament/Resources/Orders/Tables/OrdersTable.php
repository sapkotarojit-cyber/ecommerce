<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Mail\PaymentSuccessfulMail;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
                        fn ($state) => filled($state)
                            ? 'Uploaded'
                            : 'Not Uploaded'
                    )
                    ->badge()
                    ->color(
                        fn ($state) => filled($state)
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
                 * APPROVE BANK PAYMENT
                 *
                 * The email is sent after the entire payment group
                 * has become paid and the database transaction commits.
                 */
                Action::make('approvePayment')
                    ->label('Approve Payment')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Payment')
                    ->modalDescription(
                        'Approve this bank transfer? The order will be marked as paid and confirmed.'
                    )
                    ->visible(fn (Order $record) =>
                        $record->payment_method === 'bank'
                        && $record->payment_status === 'pending'
                        && filled($record->payment_receipt)
                    )
                    ->action(function (Order $record): void {
                        $result = DB::transaction(
                            function () use ($record): array {
                                $orders = Order::query()
                                    ->where('user_id', $record->user_id)
                                    ->where(
                                        'payment_group_id',
                                        $record->payment_group_id
                                    )
                                    ->orderBy('id')
                                    ->lockForUpdate()
                                    ->get();

                                if ($orders->isEmpty()) {
                                    throw new \RuntimeException(
                                        'Payment group not found.'
                                    );
                                }

                                $target = $orders->firstWhere(
                                    'id',
                                    $record->id
                                );

                                if (! $target) {
                                    throw new \RuntimeException(
                                        'Order not found in its payment group.'
                                    );
                                }

                                if ($target->payment_method !== 'bank') {
                                    throw new \RuntimeException(
                                        'This is not a bank-transfer order.'
                                    );
                                }

                                if ($target->payment_status === 'paid') {
                                    return [
                                        'newly_approved' => false,
                                        'group_fully_paid' => $orders->every(
                                            fn (Order $order) =>
                                                $order->payment_status === 'paid'
                                        ),
                                    ];
                                }

                                if (
                                    $target->payment_status !== 'pending'
                                    || ! filled($target->payment_receipt)
                                ) {
                                    throw new \RuntimeException(
                                        'This order is not eligible for approval.'
                                    );
                                }

                                $groupWasFullyPaid = $orders->every(
                                    fn (Order $order) =>
                                        $order->payment_status === 'paid'
                                );

                                $target->payment_status = 'paid';
                                $target->order_status = 'confirmed';
                                $target->save();

                                $orders = Order::query()
                                    ->where('user_id', $target->user_id)
                                    ->where(
                                        'payment_group_id',
                                        $target->payment_group_id
                                    )
                                    ->orderBy('id')
                                    ->get();

                                return [
                                    'newly_approved' => true,
                                    'group_fully_paid' => $orders->isNotEmpty()
                                        && $orders->every(
                                            fn (Order $order) =>
                                                $order->payment_status === 'paid'
                                        ),
                                    'group_was_fully_paid' => $groupWasFullyPaid,
                                    'user_id' => $target->user_id,
                                    'payment_group_id' =>
                                        $target->payment_group_id,
                                ];
                            }
                        );

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
                    ->action(function (Order $record): void {
                        $record->update([
                            'payment_status' => 'failed',
                        ]);
                    })
                    ->successNotificationTitle('Payment rejected'),

                // NORMAL EDIT
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
