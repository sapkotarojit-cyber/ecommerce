<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('category_id')
                    ->numeric(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('category'),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('dokan_id')
                    ->numeric(),
            ]);
    }
}
