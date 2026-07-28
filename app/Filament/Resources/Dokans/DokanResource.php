<?php

namespace App\Filament\Resources\Dokans;

use App\Filament\Resources\Dokans\Pages\CreateDokan;
use App\Filament\Resources\Dokans\Pages\EditDokan;
use App\Filament\Resources\Dokans\Pages\ListDokans;
use App\Filament\Resources\Dokans\Schemas\DokanForm;
use App\Filament\Resources\Dokans\Tables\DokansTable;
use App\Models\Dokan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class DokanResource extends Resource
{
    protected static ?string $model = Dokan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'company_name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return DokanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DokansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDokans::route('/'),
            'create' => CreateDokan::route('/create'),
            'edit' => EditDokan::route('/{record}/edit'),
        ];
    }
}
