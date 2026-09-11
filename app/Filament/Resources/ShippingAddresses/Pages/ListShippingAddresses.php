<?php

namespace App\Filament\Resources\ShippingAddresses\Pages;

use App\Filament\Resources\ShippingAddresses\ShippingAddressResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShippingAddresses extends ListRecords
{
    protected static string $resource = ShippingAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
