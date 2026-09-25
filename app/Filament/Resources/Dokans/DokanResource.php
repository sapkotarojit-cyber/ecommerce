<?php

namespace App\Filament\Resources\Dokans;

use App\Filament\Resources\Dokans\Pages;
use App\Models\Dokan;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class DokanResource extends Resource
{
    protected static ?string $model = Dokan::class;

    protected static UnitEnum|string|null $navigationGroup = 'Vendors & Logistics';
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Dokans';
    protected static ?string $modelLabel = 'Dokan';
    protected static ?string $pluralModelLabel = 'Dokans';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Basic Information')->schema([
                Grid::make(2)->schema([
                    TextInput::make('user_id')->label('User ID')->numeric(),
                    TextInput::make('email')->email()->required(),
                    TextInput::make('company_name')->required(),
                    TextInput::make('name')->label('Contact Person'),
                    TextInput::make('reg_no')->label('Registration Number')->required(),
                    TextInput::make('contact_number')->tel()->required(),

                    Select::make('status')
                        ->options([
                            Dokan::STATUS_PENDING => 'Pending',
                            Dokan::STATUS_APPROVED => 'Approved',
                            Dokan::STATUS_REJECTED => 'Rejected',
                        ])
                        ->required(),

                    Textarea::make('rejection_comment')
                        ->label('Rejection Comment')
                        ->rows(2),
                ]),
            ]),

            Section::make('Business Address')->schema([
                Grid::make(2)->schema([
                    TextInput::make('business_location')
                        ->label('Business Location')
                        ->required(),

                    Textarea::make('business_address')
                        ->label('Business Address')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            ]),

            Section::make('Business Information')->schema([
                Grid::make(2)->schema([
                    TextInput::make('business_reg_no')
                        ->label('Business Registration No.')
                        ->required(),

                    TextInput::make('pan_no')
                        ->label('Permanent Account Number (PAN)')
                        ->required(),

                    FileUpload::make('business_document')
                        ->label('Business Information Document')
                        ->disk('public')
                        ->directory('vendor-documents/business')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ])
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->maxSize(5120)
                        ->columnSpanFull(),
                ]),
            ]),

            Section::make('Bank Information')->schema([
                Grid::make(2)->schema([
                    TextInput::make('bank_name')
                        ->label('Bank Name')
                        ->required(),

                    TextInput::make('bank_account_name')
                        ->label('Account Holder Name')
                        ->required(),

                    TextInput::make('bank_account_number')
                        ->label('Account Number')
                        ->required(),

                    TextInput::make('bank_branch')
                        ->label('Bank Branch')
                        ->required(),

                    FileUpload::make('bank_document')
                        ->label('Bank Information Document')
                        ->disk('public')
                        ->directory('vendor-documents/bank')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ])
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->maxSize(5120)
                        ->columnSpanFull(),
                ]),
            ]),

            Section::make('Company Logo')->schema([
                FileUpload::make('logo')
                    ->label('Company Logo')
                    ->disk('public')
                    ->directory('vendor-logos')
                    ->image()
                    ->imageEditor()
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->maxSize(2048),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('contact_number'),

                TextColumn::make('business_location')
                    ->label('Location')
                    ->searchable(),

                TextColumn::make('business_reg_no')
                    ->label('Business Reg. No.')
                    ->searchable(),

                TextColumn::make('pan_no')
                    ->label('PAN')
                    ->searchable(),

                TextColumn::make('bank_name')
                    ->label('Bank'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Dokan::STATUS_APPROVED => 'success',
                        Dokan::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Dokan::STATUS_PENDING => 'Pending',
                        Dokan::STATUS_APPROVED => 'Approved',
                        Dokan::STATUS_REJECTED => 'Rejected',
                    ]),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDokans::route('/'),
            'create' => Pages\CreateDokan::route('/create'),
            'edit' => Pages\EditDokan::route('/{record}/edit'),
        ];
    }
}