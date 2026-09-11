<?php

namespace App\Filament\Resources\ShippingAddresses\Pages;

use App\Filament\Resources\ShippingAddresses\ShippingAddressResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShippingAddress extends EditRecord
{
    protected static string $resource = ShippingAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
