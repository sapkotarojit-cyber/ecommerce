<?php

namespace App\Filament\Dokan\Resources\Products\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('first_variant_image')
                    ->label('Image')
                    ->state(function ($record) {
                        $variant = $record->varients->first();

                        if (! $variant || empty($variant->images)) {
                            return null;
                        }

                        $images = is_array($variant->images)
                            ? $variant->images
                            : json_decode($variant->images, true);

                        return $images[0] ?? null;
                    })
                    ->disk('public')
                    ->size(50),

                TextColumn::make('title')
                    ->label('Title')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category_id')
                    ->label('Category ID')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('category_id')
                    ->label('Category')
                    ->formatStateUsing(function ($state) {
                        return Category::find($state)?->name ?? 'No Category';
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dokan_id')
                    ->label('Dokan ID')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('varients_count')
                    ->label('Variants')
                    ->counts('varients')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([])

            ->recordActions([
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}