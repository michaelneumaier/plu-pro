<?php

namespace App\Filament\Resources\UserListResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListCopiesRelationManager extends RelationManager
{
    protected static string $relationship = 'listCopies';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Copy History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Copied By')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('copiedList.name')
                    ->label('Copied List Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('copiedList.listItems_count')
                    ->counts('copiedList.listItems')
                    ->label('Items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Copied On')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('viewCopiedList')
                    ->label('View Copy')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => UserListResource::getUrl('view', ['record' => $record->copied_list_id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No copies yet')
            ->emptyStateDescription('This list hasn\'t been copied by anyone yet.')
            ->emptyStateIcon('heroicon-o-document-duplicate');
    }
}