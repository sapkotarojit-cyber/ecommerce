<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('tracking_number')
                    ->label('Tracking Number')
                    ->required()
                    ->disabled()
                    ->dehydrated(false),

                Select::make('user_id')
                    ->label('Customer')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->exists('users', 'id'),

                Select::make('dokan_id')
                    ->label('Vendor')
                    ->relationship('dokan', 'company_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->exists('dokans', 'id'),

                TextInput::make('shipping_address_display')
                    ->label('Shipping Address')
                    ->formatStateUsing(function ($state, $record) {
                        $shipping = $record?->shippingAddress;

                        if (! $shipping) {
                            return 'No Address';
                        }

                        return implode(', ', array_filter([
                            $shipping->name,
                            $shipping->phone,
                            $shipping->address,
                            $shipping->landmark,
                            $shipping->region,
                        ]));
                    })
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),

                TextInput::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->required(),

                Select::make('status')
                    ->label('Order Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),

                Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cod' => 'Cash on Delivery',
                        'esewa' => 'eSewa',
                        'bank' => 'Bank Transfer',
                    ])
                    ->required(),

                Select::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending Verification',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->default('pending')
                    ->required(),

               FileUpload::make('payment_receipt')
                    ->label('Payment Receipt')
                    ->disk('public')
                    ->directory('payment_receipts')
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'application/pdf',
                    ])
                    ->openable()
                    ->downloadable()
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }
}