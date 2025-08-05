<?php

namespace App\Filament\Resources\UserListResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'listItems';

    protected static ?string $recordTitleAttribute = 'plu_code_id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plu_code_id')
                    ->relationship('pluCode', 'plu')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->plu} - {$record->commodity} ({$record->variety})"),
                Forms\Components\TextInput::make('inventory_level')
                    ->numeric()
                    ->step(0.5)
                    ->default(0)
                    ->required(),
                Forms\Components\Toggle::make('organic')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('plu_code_id')
            ->columns([
                Tables\Columns\TextColumn::make('pluCode.plu')
                    ->label('PLU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('pluCode.commodity')
                    ->label('Commodity')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pluCode.variety')
                    ->label('Variety')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pluCode.size')
                    ->label('Size')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('inventory_level')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('organic')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('organic')
                    ->placeholder('All items')
                    ->trueLabel('Organic only')
                    ->falseLabel('Conventional only'),
                Tables\Filters\Filter::make('has_inventory')
                    ->query(fn (Builder $query): Builder => $query->where('inventory_level', '>', 0))
                    ->label('Has Inventory'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('importFromCSV')
                    ->label('Import from CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('csv_file')
                            ->acceptedFileTypes(['text/csv', 'application/csv'])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // Import logic would go here
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateInventory')
                        ->label('Update Inventory')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                            Forms\Components\TextInput::make('inventory_level')
                                ->label('New Inventory Level')
                                ->numeric()
                                ->step(0.5)
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['inventory_level' => $data['inventory_level']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('markAsOrganic')
                        ->label('Mark as Organic')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['organic' => true]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
