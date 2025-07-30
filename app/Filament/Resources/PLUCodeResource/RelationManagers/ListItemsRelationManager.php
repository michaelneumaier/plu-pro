<?php

namespace App\Filament\Resources\PLUCodeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'listItems';

    protected static ?string $recordTitleAttribute = 'user_list_id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('inventory_level')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_list_id')
            ->columns([
                Tables\Columns\TextColumn::make('userList.name')
                    ->label('List Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userList.user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('inventory_level')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('organic')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}