<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserListResource\Pages;
use App\Filament\Resources\UserListResource\RelationManagers;
use App\Models\UserList;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserListResource extends Resource
{
    protected static ?string $model = UserList::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('marketplace_enabled', true)->exists() ? 'warning' : 'gray';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('List Information')
                    ->description('Basic list details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn ($context) => $context === 'edit'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('share_code')
                                    ->label('Share Code')
                                    ->disabled()
                                    ->helperText('Auto-generated unique share code'),
                                Forms\Components\Toggle::make('is_public')
                                    ->label('Public List')
                                    ->helperText('Allow anyone with the share code to view this list'),
                            ]),
                    ]),

                Section::make('Marketplace Settings')
                    ->description('Control marketplace visibility and details')
                    ->schema([
                        Forms\Components\Toggle::make('marketplace_enabled')
                            ->label('Enable in Marketplace')
                            ->helperText('Make this list discoverable in the marketplace')
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $state ? $set('published_at', now()) : $set('published_at', null)),
                        Grid::make(1)
                            ->schema([
                                Forms\Components\TextInput::make('marketplace_title')
                                    ->label('Marketplace Title')
                                    ->maxLength(255)
                                    ->placeholder('e.g., "Summer Farmers Market Essentials"')
                                    ->visible(fn (callable $get) => $get('marketplace_enabled')),
                                Forms\Components\Textarea::make('marketplace_description')
                                    ->label('Marketplace Description')
                                    ->rows(4)
                                    ->maxLength(1000)
                                    ->helperText('Describe what makes this list special')
                                    ->visible(fn (callable $get) => $get('marketplace_enabled')),
                                Forms\Components\Select::make('marketplace_category')
                                    ->label('Category')
                                    ->options([
                                        'seasonal' => 'Seasonal',
                                        'organic' => 'Organic',
                                        'local' => 'Local Produce',
                                        'exotic' => 'Exotic & Specialty',
                                        'everyday' => 'Everyday Essentials',
                                        'business' => 'Business/Commercial',
                                        'educational' => 'Educational',
                                        'other' => 'Other',
                                    ])
                                    ->visible(fn (callable $get) => $get('marketplace_enabled')),
                            ]),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Published Date')
                            ->disabled()
                            ->visible(fn (callable $get) => $get('marketplace_enabled')),
                    ])
                    ->collapsible(),

                Section::make('Statistics')
                    ->description('List performance metrics')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('view_count')
                                    ->label('Views')
                                    ->disabled()
                                    ->numeric(),
                                Forms\Components\TextInput::make('copy_count')
                                    ->label('Copies')
                                    ->disabled()
                                    ->numeric(),
                                Forms\Components\TextInput::make('items_count')
                                    ->label('Total Items')
                                    ->disabled()
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record) {
                                            $component->state($record->listItems()->count());
                                        }
                                    }),
                                Forms\Components\TextInput::make('total_inventory')
                                    ->label('Total Inventory')
                                    ->disabled()
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record) {
                                            $component->state($record->listItems()->sum('inventory_level'));
                                        }
                                    }),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('listItems_count')
                    ->counts('listItems')
                    ->label('Items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('share_code')
                    ->copyable()
                    ->copyMessage('Share code copied')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),
                Tables\Columns\IconColumn::make('marketplace_enabled')
                    ->label('Marketplace')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('marketplace_category')
                    ->label('Category')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-m-eye')
                    ->iconPosition(IconPosition::Before),
                Tables\Columns\TextColumn::make('copy_count')
                    ->label('Copies')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-m-document-duplicate')
                    ->iconPosition(IconPosition::Before),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('marketplace_enabled')
                    ->label('Marketplace Status')
                    ->placeholder('All lists')
                    ->trueLabel('In marketplace')
                    ->falseLabel('Not in marketplace'),
                TernaryFilter::make('is_public')
                    ->label('Visibility')
                    ->placeholder('All lists')
                    ->trueLabel('Public lists')
                    ->falseLabel('Private lists'),
                SelectFilter::make('marketplace_category')
                    ->label('Category')
                    ->options([
                        'seasonal' => 'Seasonal',
                        'organic' => 'Organic',
                        'local' => 'Local Produce',
                        'exotic' => 'Exotic & Specialty',
                        'everyday' => 'Everyday Essentials',
                        'business' => 'Business/Commercial',
                        'educational' => 'Educational',
                        'other' => 'Other',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('popular')
                    ->label('Popular Lists')
                    ->query(fn (Builder $query): Builder => $query->where('view_count', '>=', 100)->orWhere('copy_count', '>=', 10)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggleMarketplace')
                    ->label(fn (UserList $record) => $record->marketplace_enabled ? 'Remove from Marketplace' : 'Add to Marketplace')
                    ->icon(fn (UserList $record) => $record->marketplace_enabled ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (UserList $record) => $record->marketplace_enabled ? 'danger' : 'success')
                    ->action(function (UserList $record) {
                        $record->update([
                            'marketplace_enabled' => ! $record->marketplace_enabled,
                            'published_at' => ! $record->marketplace_enabled ? now() : null,
                        ]);

                        Notification::make()
                            ->title($record->marketplace_enabled ? 'Added to marketplace' : 'Removed from marketplace')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('feature')
                    ->label('Feature List')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(function (UserList $record) {
                        // In a real implementation, you might have a featured flag
                        Notification::make()
                            ->title('List featured successfully')
                            ->body('This list is now featured in the marketplace.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (UserList $record) => $record->marketplace_enabled && auth()->user()->can('feature_lists')),
                Tables\Actions\Action::make('viewQR')
                    ->label('View QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->modalContent(fn (UserList $record) => view('filament.resources.user-list-resource.modals.qr-code', ['list' => $record]))
                    ->modalSubmitAction(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->can('delete_all_lists')),
                    Tables\Actions\BulkAction::make('removeFromMarketplace')
                        ->label('Remove from Marketplace')
                        ->icon('heroicon-o-eye-slash')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'marketplace_enabled' => false,
                                    'published_at' => null,
                                ]);
                            }

                            Notification::make()
                                ->title('Lists removed from marketplace')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->visible(fn (): bool => auth()->user()->can('moderate_marketplace')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ListItemsRelationManager::class,
            RelationManagers\ListCopiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserLists::route('/'),
            'create' => Pages\CreateUserList::route('/create'),
            'view' => Pages\ViewUserList::route('/{record}'),
            'edit' => Pages\EditUserList::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'marketplace_title', 'marketplace_description'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Owner' => $record->user->name,
            'Items' => $record->listItems()->count(),
            'Marketplace' => $record->marketplace_enabled ? 'Yes' : 'No',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(! auth()->user()->can('view_all_lists'), function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
    }
}
