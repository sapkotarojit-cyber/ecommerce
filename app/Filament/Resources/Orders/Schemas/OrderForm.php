<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tracking_number')
                    ->required()
                    ->default(fn () => 'ORD-' . strtoupper(uniqid())),

                    

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->exists('users', 'id'),

               Select::make('dokan_id')
                    ->relationship('dokan', 'company_name') 
                    ->searchable()
                    ->preload()
                    ->required()
                    ->exists('dokans', 'id'),

                TextInput::make('shipping_address_display')
                    ->label('Shipping Address')
                    ->formatStateUsing(function ($state, $record) {
                        $shipping = $record->shippingAddress;

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
                    ->dehydrated(false),

                TextInput::make('total_amount')
                    ->numeric()
                    ->required(),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),

                Select::make('payment_method')
                    ->options([
                        'cod' => 'Cash on Delivery',
                        'online' => 'Online Payment',
                    ])
                    ->default('cod')
                    ->required(),

                Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->default('pending')
                    ->required(),
                    
FileUpload::make('payment_receipt')
    ->label('Payment Receipt')
    ->disk('public')
    ->directory('payment_receipts')
    ->image()
    ->imagePreviewHeight('300')
    ->openable()
    ->downloadable()
    ->disabled()
    ->dehydrated(false)
    ->columnSpanFull(),
            ]);
    }
}