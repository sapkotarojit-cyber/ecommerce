<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
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
                ImageColumn::make('variant_image')
                    ->label('Image')
                    ->state(function (Product $record) {
                        $firstVariant = $record->varients->first();
                        
                        if ($firstVariant && !empty($firstVariant->images)) {
                            // Returns the first image path from the variant's 'images' array
                            return is_array($firstVariant->images) 
                                ? $firstVariant->images[0] 
                                : $firstVariant->images;
                        }

                        return null;
                    })
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('category_id')
                    ->numeric()
                    ->sortable(),
               TextColumn::make('category_id')
                    ->label('Category')
                    ->formatStateUsing(function ($state) {
                        return \App\Models\Category::find($state)?->name ?? 'No Category';
                    })
                    ->sortable(),
                TextColumn::make('dokan_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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