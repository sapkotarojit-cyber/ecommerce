<?php

namespace App\Filament\Dokan\Resources\ReturnRequests;

use App\Filament\Dokan\Resources\ReturnRequests\Pages\EditReturnRequest;
use App\Filament\Dokan\Resources\ReturnRequests\Pages\ListReturnRequests;
use App\Filament\Dokan\Resources\ReturnRequests\Schemas\ReturnRequestForm;
use App\Filament\Dokan\Resources\ReturnRequests\Tables\ReturnRequestsTable;
use App\Models\ReturnRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReturnRequestResource extends Resource
{
    protected static ?string $model = ReturnRequest::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Return Requests';

    protected static ?string $modelLabel = 'Return Request';

    protected static ?string $pluralModelLabel = 'Return Requests';

    public static function form(Schema $schema): Schema
    {
        return ReturnRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReturnRequestsTable::configure($table);
    }

    /**
     * Only show return requests belonging to
     * the currently logged-in vendor.
     */
    public static function getEloquentQuery(): Builder
    {
        $dokan = Auth::guard('dokan')->user();

        return parent::getEloquentQuery()
            ->where('dokan_id', $dokan->id)
            ->with([
                'user',
                'dokan',
                'order.order_items.product',
                'order.order_items.varient',
            ]);
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
            'index' => ListReturnRequests::route('/'),
            'edit' => EditReturnRequest::route('/{record}/edit'),
        ];
    }
}