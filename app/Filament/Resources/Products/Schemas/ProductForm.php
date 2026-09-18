<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Dokan;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $isVendor = auth()->guard('dokan')->check();

        $categorySelect = Select::make('category_id')
            ->label('Category')
            ->relationship('categories', 'name') // 👈 Points to the updated model relationship
            ->searchable()
            ->preload()
            ->required();

        if (! $isVendor) {
            $categorySelect->createOptionForm([
                TextInput::make('name')
                    ->label('Category Name')
                    ->required()
                    ->unique('categories', 'name'),
                TextInput::make('slug')
                    ->label('Category Slug')
                    ->required()
                    ->unique('categories', 'slug'),
            ]);
        }

        return $schema
            ->components([
                $categorySelect,

                TextInput::make('title')
                    ->required(),

                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),

                Hidden::make('dokan_id')
                    ->default(function () {
                        $user = Filament::auth()->user() 
                            ?? auth()->guard('dokan')->user();

                        if (! $user) {
                            return null;
                        }

                        return $user instanceof Dokan 
                            ? $user->id 
                            : (Dokan::where('user_id', $user->id)->value('id') ?? $user->dokan_id);
                    }),
            ]);
    }
}