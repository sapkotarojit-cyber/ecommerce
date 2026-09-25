<?php

namespace App\Filament\Resources\Dokans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class DokanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),

              TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->dehydrateStateUsing(fn (string $state) => Hash::make($state)),

                TextInput::make('company_name')
                    ->required(),

                TextInput::make('logo')
                    ->required(),

                TextInput::make('reg_no')
                    ->required(),

                TextInput::make('contact_number')
                    ->required(),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required()
                    ->live(),

                Textarea::make('rejection_comment')
                    ->label('Rejection Comment')
                    ->placeholder('Enter the reason for rejecting this vendor application...')
                    ->rows(4)
                    ->visible(fn (Get $get): bool => $get('status') === 'rejected')
                    ->required(fn (Get $get): bool => $get('status') === 'rejected'),
            ]);
    }
}