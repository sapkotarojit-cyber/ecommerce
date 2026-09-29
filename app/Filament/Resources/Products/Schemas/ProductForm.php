<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
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
    ->options(function () {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    })
    ->searchable()
    ->preload()
    ->required()
    ->createOptionForm([
        TextInput::make('name')
            ->label('Category Name')
            ->required()
            ->maxLength(255)
            ->unique(
                table: 'categories',
                column: 'name'
            ),

        TextInput::make('slug')
            ->label('Category Slug')
            ->required()
            ->maxLength(255)
            ->unique(
                table: 'categories',
                column: 'slug'
            ),
    ])
    ->createOptionUsing(function (array $data): int {
        $category = Category::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'is_active' => true,
        ]);

        return $category->getKey();
    });

        return $schema->components([

            /*
             * CATEGORY
             */
            $categorySelect,

            /*
             * PRODUCT TITLE
             */
            TextInput::make('title')
                ->required()
                ->maxLength(255),

            /*
             * DESCRIPTION
             */
            Textarea::make('description')
                ->required()
                ->columnSpanFull(),

            /*
             * PRODUCT VARIANTS
             */
            Repeater::make('varients')
                ->relationship('varients')
                ->schema([

                    TextInput::make('title')
                        ->label('Variant Title (e.g. Red / XL)')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('price')
                        ->label('Price')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rs.')
                        ->required(),

                    TextInput::make('discount')
                        ->label('Discount')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0)
                        ->suffix('%'),

                    TextInput::make('qty')
                        ->label('Stock Quantity')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->default(1),

                    FileUpload::make('images')
                        ->label('Product Images')
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

            /*
             * DOKAN
             */
            Hidden::make('dokan_id')
                ->default(function () {
                    $user = Filament::auth()->user()
                        ?? auth()->guard('dokan')->user();

                    if (! $user) {
                        return null;
                    }

                    if ($user instanceof Dokan) {
                        return $user->id;
                    }

                    return Dokan::where('user_id', $user->id)->value('id')
                        ?? $user->dokan_id;
                }),
        ]);
    }
}