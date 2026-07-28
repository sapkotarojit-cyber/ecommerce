<?php

namespace App\Filament\Resources\Dokans\Pages;

use App\Filament\Resources\Dokans\DokanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDokans extends ListRecords
{
    protected static string $resource = DokanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
