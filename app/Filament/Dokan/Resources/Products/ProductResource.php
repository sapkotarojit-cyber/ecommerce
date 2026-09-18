<?php

namespace App\Filament\Dokan\Resources\Products;

use App\Filament\Dokan\Resources\Products\Pages\CreateProduct;
use App\Filament\Dokan\Resources\Products\Pages\EditProduct;
use App\Filament\Dokan\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Dokan;
use App\Models\Product;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static UnitEnum|string|null $navigationGroup = 'E-Commerce';

    protected static ?int $navigationSort = 3;

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

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user() 
            ?? auth()->guard('dokan')->user() 
            ?? auth()->user();

        if (! $user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        if ($user instanceof Dokan) {
            $dokanId = $user->id;
        } else {
            $dokanId = Dokan::where('user_id', $user->id)->value('id') 
                ?? $user->dokan_id 
                ?? $user->id;
        }

        if (! $dokanId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('dokan_id', $dokanId);
    }
}