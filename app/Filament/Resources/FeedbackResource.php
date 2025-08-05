<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Models\Feedback;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'Support & Feedback';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'open')->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Feedback Details')
                    ->description('Core feedback information from the user')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('type')
                                ->options([
                                    'bug' => 'Bug Report',
                                    'feature' => 'Feature Request',
                                    'improvement' => 'Improvement',
                                    'general' => 'General Feedback',
                                ])
                                ->required()
                                ->native(false),
                            Forms\Components\Select::make('priority')
                                ->options([
                                    'low' => 'Low',
                                    'medium' => 'Medium',
                                    'high' => 'High',
                                    'critical' => 'Critical',
                                ])
                                ->required()
                                ->native(false),
                        ]),
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('User Information')
                    ->description('Details about the user who submitted this feedback')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->disabled()
                                ->dehydrated(false),
                            Forms\Components\DateTimePicker::make('created_at')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),

                Section::make('Admin Management')
                    ->description('Administrative actions and responses')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'open' => 'Open',
                                    'in_progress' => 'In Progress',
                                    'resolved' => 'Resolved',
                                    'closed' => 'Closed',
                                ])
                                ->required()
                                ->native(false),
                            Forms\Components\Select::make('assigned_admin_id')
                                ->relationship('assignedAdmin', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Assign to admin'),
                        ]),
                        Forms\Components\Textarea::make('admin_response')
                            ->label('Admin Response')
                            ->rows(3)
                            ->placeholder('Optional response to the user...')
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            Forms\Components\DateTimePicker::make('responded_at')
                                ->disabled()
                                ->dehydrated(false),
                            Forms\Components\DateTimePicker::make('resolved_at')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),

                Section::make('Metadata')
                    ->description('Technical information collected when feedback was submitted')
                    ->collapsed()
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->addable(false)
                            ->deletable(false)
                            ->editableKeys(false)
                            ->editableValues(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'danger' => 'bug',
                        'warning' => 'feature',
                        'info' => 'improvement',
                        'gray' => 'general',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bug' => 'Bug',
                        'feature' => 'Feature',
                        'improvement' => 'Improvement',
                        'general' => 'General',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->colors([
                        'danger' => 'critical',
                        'warning' => 'high',
                        'info' => 'medium',
                        'gray' => 'low',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'open',
                        'info' => 'in_progress',
                        'success' => 'resolved',
                        'gray' => 'closed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_progress' => 'In Progress',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Anonymous'),
                Tables\Columns\TextColumn::make('assignedAdmin.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($state) => $state->format('M j, Y g:i A')),
                Tables\Columns\TextColumn::make('resolved_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder('Not resolved')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ])
                    ->multiple()
                    ->default(['open', 'in_progress']),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'bug' => 'Bug Report',
                        'feature' => 'Feature Request',
                        'improvement' => 'Improvement',
                        'general' => 'General Feedback',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'critical' => 'Critical',
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('assigned_admin_id')
                    ->relationship('assignedAdmin', 'name')
                    ->label('Assigned Admin')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Forms\Components\Select::make('admin_id')
                            ->label('Assign to Admin')
                            ->options(User::where('role', 'admin')->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (Feedback $record, array $data) {
                        $admin = User::find($data['admin_id']);
                        $record->assignTo($admin);

                        Notification::make()
                            ->title('Feedback assigned successfully')
                            ->body("Assigned to {$admin->name}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Feedback $record) => ! $record->isResolved())
                    ->color('info'),
                Tables\Actions\Action::make('resolve')
                    ->label('Mark Resolved')
                    ->icon('heroicon-o-check-circle')
                    ->form([
                        Forms\Components\Textarea::make('admin_response')
                            ->label('Response (optional)')
                            ->rows(3),
                    ])
                    ->action(function (Feedback $record, array $data) {
                        $record->markAsResolved($data['admin_response'] ?? null);

                        Notification::make()
                            ->title('Feedback marked as resolved')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Feedback $record) => ! $record->isResolved())
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'open' => 'Open',
                                    'in_progress' => 'In Progress',
                                    'resolved' => 'Resolved',
                                    'closed' => 'Closed',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                                if ($data['status'] === 'resolved') {
                                    $record->update(['resolved_at' => now()]);
                                }
                            }

                            Notification::make()
                                ->title('Status updated successfully')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->isAdmin()),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
            'create' => Pages\CreateFeedback::route('/create'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Users submit feedback through the frontend
    }
}
