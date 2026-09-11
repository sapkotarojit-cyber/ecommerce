<?php

namespace App\Filament\Resources\Dokans\Pages;

use App\Filament\Resources\Dokans\DokanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDokan extends EditRecord
{
    protected static string $resource = DokanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
