<?php

namespace App\Filament\Widgets;

use App\Models\Dokan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveDokans extends BaseWidget
{
    protected static ?string $heading = 'Active Vendors';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Dokan::query()
                    ->where('status', Dokan::STATUS_APPROVED)
                    ->withCount('products')
            )
            ->columns([
                TextColumn::make('company_name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('contact_number')
                    ->label('Contact')
                    ->searchable(),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25]);
    }
}