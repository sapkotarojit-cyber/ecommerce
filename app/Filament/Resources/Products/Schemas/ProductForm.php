<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Dokan;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $categorySelect = Select::make('category_id')
            ->label('Category')
            ->relationship('categories', 'name')
            ->searchable()
            ->preload()
            ->required()
            // 👈 Enabled for everyone (both Admins and Dokan vendors)
            ->createOptionForm([
                TextInput::make('name')
                    ->label('Category Name')
                    ->required()
                    ->unique('categories', 'name'),
                TextInput::make('slug')
                    ->label('Category Slug')
                    ->required()
                    ->unique('categories', 'slug'),
            ]);

        return $schema
            ->components([
                $categorySelect,

                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),

                // Product Variants Repeater
                Repeater::make('varients')
                    ->relationship('varients')
                    ->schema([
                        TextInput::make('title')
                            ->label('Variant Title (e.g. Red / XL)')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required(),

                        TextInput::make('discount')
                            ->numeric()
                            ->default(0)
                            ->suffix('%'),

                        TextInput::make('qty')
                            ->label('Stock Quantity')
                            ->numeric()
                            ->required()
                            ->default(1),

                        FileUpload::make('images')
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('product-variants')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->columnSpanFull()
                    ->required()
                    ->label('Product Variants'),

                Hidden::make('dokan_id')
                    ->default(function () {
                        $user = Filament::auth()->user() 
                            ?? auth()->guard('dokan')->user();

                        if (! $user) {
                            return null;
                        }

                        return $user instanceof Dokan 
                            ? $user->id 
                            : (Dokan::where('user_id', $user->id)->value('id') ?? $user->dokan_id);
                    }),
            ]);
    }
}