<?php

namespace App\Filament\Resources\ShippingAddresses\Pages;

use App\Filament\Resources\ShippingAddresses\ShippingAddressResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingAddress extends CreateRecord
{
    protected static string $resource = ShippingAddressResource::class;
}
