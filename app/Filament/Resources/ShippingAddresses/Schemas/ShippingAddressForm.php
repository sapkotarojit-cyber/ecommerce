<?php

namespace App\Filament\Resources\ShippingAddresses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShippingAddressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name') // Automatically links to existing users
                    ->searchable()
                    ->preload()
                    ->required()
                    ->exists('users', 'id'), // Prevents invalid ID submissions

                TextInput::make('title')
                    ->required(),

                TextInput::make('contact_no')
                    ->required(),

                TextInput::make('full_address')
                    ->required(),

                Toggle::make('is_default')
                    ->default(false),
            ]);
    }
}