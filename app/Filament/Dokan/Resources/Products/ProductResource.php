<?php

namespace App\Filament\Dokan\Resources\Products;

use App\Filament\Dokan\Resources\Products\Pages\CreateProduct;
use App\Filament\Dokan\Resources\Products\Pages\EditProduct;
use App\Filament\Dokan\Resources\Products\Pages\ListProducts;
use App\Filament\Dokan\Resources\Products\Schemas\ProductForm;
use App\Filament\Dokan\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $dokan = Auth::guard('dokan')->user();

        if (!$dokan) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('dokan_id', $dokan->id);
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
