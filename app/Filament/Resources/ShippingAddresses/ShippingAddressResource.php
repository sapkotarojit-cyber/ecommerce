<?php

namespace App\Filament\Resources\ShippingAddresses;

use App\Filament\Resources\ShippingAddresses\Pages\CreateShippingAddress;
use App\Filament\Resources\ShippingAddresses\Pages\EditShippingAddress;
use App\Filament\Resources\ShippingAddresses\Pages\ListShippingAddresses;
use App\Filament\Resources\ShippingAddresses\Schemas\ShippingAddressForm;
use App\Filament\Resources\ShippingAddresses\Tables\ShippingAddressesTable;
use App\Models\ShippingAddress;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ShippingAddressResource extends Resource
{
    protected static ?string $model = ShippingAddress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static UnitEnum|string|null $navigationGroup = 'Vendors & Logistics';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ShippingAddressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingAddressesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }



   public static function getPages(): array
{
    return [
        'index' => ListShippingAddresses::route('/'),
        'edit' => EditShippingAddress::route('/{record}/edit'),
    ];
}
}
