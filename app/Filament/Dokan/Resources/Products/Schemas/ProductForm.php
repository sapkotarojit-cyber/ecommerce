<?php

namespace App\Filament\Dokan\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Category Name')
                                ->required()
                                ->unique('categories', 'name'),
                        ])
                        ->required(),
                    RichEditor::make('description')
                        ->required()
                        ->columnSpanFull(),
                    // TextInput::make('dokan_id')
                    //     ->required()
                    //     ->numeric(),
                ])->columnSpanFull(),

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