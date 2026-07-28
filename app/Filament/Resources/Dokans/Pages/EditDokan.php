<?php

namespace App\Filament\Resources\Dokans\Pages;

use App\Filament\Resources\Dokans\DokanResource;
use App\Mail\DokanRequestApproval;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Override;

class EditDokan extends EditRecord
{
    protected static string $resource = DokanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if($data['status'] == 'approved'){
            $password = rand(100000,99999);
            $data['password'] = Hash::make($password);
            Mail::to($data['email'])->send(new DokanRequestApproval($data, $password));
        }
        return parent::mutateFormDataBeforeSave($data);
    }

}


