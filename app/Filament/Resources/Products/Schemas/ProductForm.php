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
use Illuminate\Validation\Rule;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        /*
         * CATEGORY
         *
         * Only active categories are selectable.
         */
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
            ->rules([
                'integer',
                'exists:categories,id',
            ])
            ->createOptionForm([
                TextInput::make('name')
                    ->label('Category Name')
                    ->required()
                    ->string()
                    ->minLength(1)
                    ->maxLength(255)
                    ->rules([
                        'string',
                        'max:255',
                    ])
                    ->unique(
                        table: 'categories',
                        column: 'name'
                    ),

                TextInput::make('slug')
                    ->label('Category Slug')
                    ->required()
                    ->string()
                    ->minLength(1)
                    ->maxLength(255)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->rules([
                        'string',
                        'max:255',
                        'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    ])
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
                ->label('Product Title')
                ->required()
                ->string()
                ->minLength(1)
                ->maxLength(255)
                ->rules([
                    'string',
                    'max:255',
                ]),

            /*
             * DESCRIPTION
             */
            Textarea::make('description')
                ->label('Description')
                ->required()
                ->string()
                ->minLength(1)
                ->maxLength(10000)
                ->rules([
                    'string',
                    'max:10000',
                ])
                ->columnSpanFull(),

            /*
             * PRODUCT VARIANTS
             */
            Repeater::make('varients')
                ->relationship('varients')
                ->schema([

                    /*
                     * VARIANT TITLE
                     *
                     * Database also enforces:
                     * product_id + title = unique
                     */
                    TextInput::make('title')
                        ->label('Variant Title (e.g. Red / XL)')
                        ->required()
                        ->string()
                        ->minLength(1)
                        ->maxLength(255)
                        ->rules([
                            'string',
                            'max:255',
                        ]),

                    /*
                     * PRICE
                     *
                     * Database column is DECIMAL(12,2).
                     */
                    TextInput::make('price')
                        ->label('Price')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(9999999999.99)
                        ->step(0.01)
                        ->prefix('Rs.')
                        ->rules([
                            'numeric',
                            'min:0',
                            'max:9999999999.99',
                        ]),

                    /*
                     * DISCOUNT
                     */
                    TextInput::make('discount')
                        ->label('Discount')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0)
                        ->suffix('%')
                        ->rules([
                            'integer',
                            'min:0',
                            'max:100',
                        ]),

                    /*
                     * STOCK QUANTITY
                     */
                    TextInput::make('qty')
                        ->label('Stock Quantity')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(2147483647)
                        ->default(1)
                        ->rules([
                            'integer',
                            'min:0',
                            'max:2147483647',
                        ]),

                    /*
                     * PRODUCT IMAGES
                     */

                       FileUpload::make('images')
                        ->label('Product Images')
                        ->multiple()
                        ->image()
                        ->disk('public')
                        ->directory('product-variants')
                        ->required(false)
                        ->columnSpanFull(),

                ])
                ->columns(3)
                ->defaultItems(1)
                ->columnSpanFull()
                ->label('Product Variants'),
                

            /*
             * DOKAN
             *
             * Do not allow the browser/user to freely choose
             * another vendor.
             */
Select::make('dokan_id')
    ->label('Seller')
    ->options(function (): array {
        return Dokan::query()
            ->where('status', Dokan::STATUS_APPROVED)
            ->orderBy('company_name')
            ->get()
            ->mapWithKeys(fn (Dokan $dokan): array => [
                $dokan->id => ($dokan->company_name ?: $dokan->name ?: $dokan->email)
                    . ' (' . $dokan->email . ')',
            ])
            ->all();
    })
    ->searchable()
    ->preload()
    ->required()
    ->rules([
        'integer',
        'exists:dokans,id',
    ]),
            ]);
    }
}

