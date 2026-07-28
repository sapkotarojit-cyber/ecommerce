<?php

namespace App\Filament\Resources\Dokans\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DokanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                // TextInput::make('password')
                //     ->password()
                //     ->default(null),
                TextInput::make('company_name')
                    ->required(),
                    TextInput::make('reg_no')
                    ->required(),
                    TextInput::make('contact_number')
                    ->required(),
                    Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->required(),
                    FileUpload::make('logo')
                        ->required(),
            ]);
    }
}
