<?php

namespace App\Filament\Dokan\Resources\ReturnRequests\Pages;

use App\Filament\Dokan\Resources\ReturnRequests\ReturnRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReturnRequest extends EditRecord
{
    protected static string $resource = ReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
