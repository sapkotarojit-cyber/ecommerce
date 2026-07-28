<?php

namespace App\Filament\Dokan\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Exceptions\ReportableHandler;
use Symfony\Contracts\Service\Attribute\Required;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product')
                ->schema([
                    TextInput::make('title')
                    ->required(),
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                // TextInput::make('dokan_id')
                //     ->required()
                //     ->numeric(),
                ])->ColumnSpanFull(),

                Repeater::make('varients')
                ->relationship('varients')
                ->columnSpanFull()
                ->grid(2)
                ->schema([
                     TextInput::make('title')
                    ->required(),
                     TextInput::make('price')
                     ->numeric()
                     ->prefix('Rs.')
                    ->required(),
                     TextInput::make('discount')
                      ->numeric()
                     ->suffix('%')
                     ->default(0)

                    ->required(),
                     TextInput::make('qty')
                      ->numeric()
                     ->prefix('Rs.')
                    ->required(),
                    FileUpload::make('images')
                    ->required()
                    ->multiple(),
                ])
            ]);
    }
}
