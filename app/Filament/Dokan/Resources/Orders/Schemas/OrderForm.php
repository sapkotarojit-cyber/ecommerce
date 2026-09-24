<?php

namespace App\Filament\Dokan\Resources\Orders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Order Information')
                    ->schema([

                        TextInput::make('tracking_number')
                            ->label('Tracking Number')
                            ->disabled(),

                        Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('Rs.')
                            ->disabled()
                            ->dehydrated(false),

                    ])
                    ->columns(2),

                Section::make('Shipping Address')
                    ->schema([

                        Select::make('shipping_address_id')
                            ->label('Customer Shipping Address')
                            ->relationship(
                                'shippingAddress',
                                'address'
                            )
                            ->disabled()
                            ->dehydrated(false),

                    ]),

                Section::make('Payment Information')
                    ->schema([

                        Select::make('order_status')
                            ->label('Order Status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->required(),

                        TextInput::make('payment_method')
                            ->label('Payment Method')
                            ->disabled()
                            ->dehydrated(false),

                        FileUpload::make('payment_receipt')
                            ->label('Payment Receipt')
                            ->disk('public')
                            ->image()
                            ->openable()
                            ->downloadable()
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),

                    ])
                    ->columns(3),
            ]);
    }
}