<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PLUCodeResource\Pages;
use App\Filament\Resources\PLUCodeResource\RelationManagers;
use App\Models\PLUCode;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PLUCodeResource extends Resource
{
    protected static ?string $model = PLUCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'PLU Management';

    protected static ?string $recordTitleAttribute = 'plu';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->description('Core PLU code information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('plu')
                                    ->label('PLU Code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(5)
                                    ->placeholder('3000')
                                    ->helperText('5-digit PLU code'),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'Conventional' => 'Conventional',
                                        'Organic' => 'Organic',
                                    ])
                                    ->required()
                                    ->native(false),
                                Forms\Components\Select::make('category')
                                    ->options([
                                        'Fresh Fruits' => 'Fresh Fruits',
                                        'Fresh Vegetables' => 'Fresh Vegetables',
                                        'Fresh Herbs' => 'Fresh Herbs',
                                        'Nuts' => 'Nuts',
                                        'Dried Fruits & Vegetables' => 'Dried Fruits & Vegetables',
                                    ])
                                    ->required()
                                    ->native(false),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('commodity')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Apples'),
                                Forms\Components\TextInput::make('variety')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Red Delicious'),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Additional Details')
                    ->description('Size, measurements, and other specifications')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('size')
                                    ->maxLength(255)
                                    ->placeholder('Large'),
                                Forms\Components\TextInput::make('measures_na')
                                    ->label('Measures (NA)')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('measures_row')
                                    ->label('Measures (ROW)')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Textarea::make('restrictions')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Scientific & Alternative Names')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('botanical')
                                    ->label('Botanical Name')
                                    ->maxLength(255)
                                    ->placeholder('Malus domestica'),
                                Forms\Components\TextInput::make('aka')
                                    ->label('Also Known As')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Status & Metadata')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'Active' => 'Active',
                                        'Inactive' => 'Inactive',
                                        'Pending' => 'Pending',
                                        'Retired' => 'Retired',
                                    ])
                                    ->required()
                                    ->default('Active')
                                    ->native(false),
                                Forms\Components\TextInput::make('consumer_usage_tier')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(5),
                                Forms\Components\Select::make('language')
                                    ->options([
                                        'en' => 'English',
                                        'es' => 'Spanish',
                                        'fr' => 'French',
                                    ])
                                    ->default('en')
                                    ->native(false),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('link')
                                    ->label('Reference URL')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('has_image')
                                    ->label('Has Image')
                                    ->helperText('Indicates if a product image is available'),
                            ]),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('updated_by')
                            ->label('Last Updated By')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plu')
                    ->label('PLU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('PLU code copied')
                    ->weight('bold')
                    ->icon('heroicon-m-hashtag')
                    ->iconPosition(IconPosition::Before),
                Tables\Columns\TextColumn::make('commodity')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('variety')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'success' => 'Conventional',
                        'warning' => 'Organic',
                    ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('size')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Inactive',
                        'warning' => 'Pending',
                        'gray' => 'Retired',
                    ]),
                Tables\Columns\IconColumn::make('has_image')
                    ->label('Image')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('consumer_usage_tier')
                    ->label('Usage Tier')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'Conventional' => 'Conventional',
                        'Organic' => 'Organic',
                    ]),
                SelectFilter::make('category')
                    ->options([
                        'Fresh Fruits' => 'Fresh Fruits',
                        'Fresh Vegetables' => 'Fresh Vegetables',
                        'Fresh Herbs' => 'Fresh Herbs',
                        'Nuts' => 'Nuts',
                        'Dried Fruits & Vegetables' => 'Dried Fruits & Vegetables',
                    ])
                    ->multiple(),
                SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                        'Pending' => 'Pending',
                        'Retired' => 'Retired',
                    ]),
                TernaryFilter::make('has_image')
                    ->label('Has Image')
                    ->placeholder('All PLUs')
                    ->trueLabel('With images')
                    ->falseLabel('Without images'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadImage')
                    ->label('Download Image')
                    ->icon('heroicon-o-photo')
                    ->action(function (PLUCode $record) {
                        // Dispatch job to download image
                        dispatch(new \App\Jobs\DownloadPLUImage($record));
                    })
                    ->visible(fn (PLUCode $record) => ! $record->has_image)
                    ->requiresConfirmation()
                    ->color('info'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('downloadImages')
                        ->label('Download Images')
                        ->icon('heroicon-o-photo')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (! $record->has_image) {
                                    dispatch(new \App\Jobs\DownloadPLUImage($record));
                                }
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'Active' => 'Active',
                                    'Inactive' => 'Inactive',
                                    'Pending' => 'Pending',
                                    'Retired' => 'Retired',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('plu', 'asc')
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ListItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPLUCodes::route('/'),
            'create' => Pages\CreatePLUCode::route('/create'),
            'view' => Pages\ViewPLUCode::route('/{record}'),
            'edit' => Pages\EditPLUCode::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['plu', 'commodity', 'variety', 'botanical', 'aka'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'PLU' => $record->plu,
            'Type' => $record->type,
            'Category' => $record->category,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
