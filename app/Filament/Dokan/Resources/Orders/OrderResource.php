<?php

namespace App\Filament\Dokan\Resources\Orders;

use App\Filament\Dokan\Resources\Orders\Pages\EditOrder;
use App\Filament\Dokan\Resources\Orders\Pages\ListOrders;
use App\Filament\Dokan\Resources\Orders\Schemas\OrderForm;
use App\Filament\Dokan\Resources\Orders\Tables\OrdersTable;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedShoppingBag;

    protected static UnitEnum|string|null $navigationGroup = 'E-Commerce';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Orders';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $dokan = Auth::guard('dokan')->user();

        if (!$dokan) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('dokan_id', $dokan->id)
            ->with([
                'user',
                'shippingAddress',
                'orderItems.product',
                'orderItems.varient',
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    
    
    public static function getRelations(): array
{
    return [
        OrderItemsRelationManager::class,
    ];
}

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}