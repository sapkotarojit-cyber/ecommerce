<?php

namespace App\Filament\Resources\Carts\Schemas;

use App\Models\ProductVarient;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('dokan_id')
                    ->label('Dokan')
                    ->relationship('dokan', 'company_name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),

                Select::make('varient_id')
                    ->label('Product Variant')
                    ->relationship('varient', 'title')
                    ->options(function (Get $get) {
                        $productId = $get('product_id');
                        if (! $productId) {
                            return ProductVarient::pluck('title', 'id');
                        }

                        return ProductVarient::where('product_id', $productId)->pluck('title', 'id');
                    })
                    ->searchable()
                    ->nullable(),

                TextInput::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),
            ]);
    }
}