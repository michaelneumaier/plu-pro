<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Information')
                    ->description('Basic user account information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->label(fn (string $context): string => $context === 'create' ? 'Password' : 'New Password')
                                    ->helperText(fn (string $context): string => $context === 'create' ? '' : 'Leave blank to keep current password'),
                                Forms\Components\Select::make('role')
                                    ->options([
                                        'admin' => 'Administrator',
                                        'manager' => 'Manager',
                                        'user' => 'User',
                                    ])
                                    ->required()
                                    ->default('user')
                                    ->native(false)
                                    ->visible(fn (): bool => auth()->user()->isAdmin()),
                            ]),
                    ]),

                Section::make('Profile & Security')
                    ->description('Profile photo and security settings')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\FileUpload::make('profile_photo_path')
                                    ->label('Profile Photo')
                                    ->image()
                                    ->avatar()
                                    ->directory('profile-photos')
                                    ->visibility('public'),
                                Forms\Components\DateTimePicker::make('email_verified_at')
                                    ->label('Email Verified At')
                                    ->disabled(),
                            ]),
                        Forms\Components\Toggle::make('two_factor_enabled')
                            ->label('Two-Factor Authentication')
                            ->helperText('Enable two-factor authentication for enhanced security')
                            ->reactive()
                            ->afterStateHydrated(function (Forms\Components\Toggle $component, $state, $record) {
                                if ($record) {
                                    $component->state($record->two_factor_secret !== null);
                                }
                            })
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->collapsible(),

                Section::make('Permissions')
                    ->description('User roles and permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->columns(2)
                            ->helperText('Assign Spatie permission roles')
                            ->visible(fn (): bool => auth()->user()->isAdmin()),
                    ])
                    ->collapsible()
                    ->visible(fn (): bool => auth()->user()->isAdmin()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo_url')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied'),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'manager',
                        'success' => 'user',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('userLists_count')
                    ->counts('userLists')
                    ->label('Lists')
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn ($record) => $record->email_verified_at !== null),
                Tables\Columns\IconColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->getStateUsing(fn ($record) => $record->two_factor_secret !== null),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'manager' => 'Manager',
                        'user' => 'User',
                    ]),
                TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable()
                    ->placeholder('All users')
                    ->trueLabel('Verified users')
                    ->falseLabel('Unverified users'),
                TernaryFilter::make('two_factor_secret')
                    ->label('Two-Factor Auth')
                    ->nullable()
                    ->placeholder('All users')
                    ->trueLabel('2FA enabled')
                    ->falseLabel('2FA disabled')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('two_factor_secret'),
                        false: fn (Builder $query) => $query->whereNull('two_factor_secret'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('changePassword')
                    ->label('Change Password')
                    ->icon('heroicon-o-key')
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->confirmed()
                            ->label('New Password'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required()
                            ->label('Confirm Password'),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update(['password' => Hash::make($data['password'])]);
                        Notification::make()
                            ->title('Password updated successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Change Password')
                    ->modalButton('Update Password')
                    ->visible(fn (): bool => auth()->user()->isAdmin()),
                Tables\Actions\Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-user')
                    ->requiresConfirmation()
                    ->color('warning')
                    ->visible(fn (User $record): bool => auth()->user()->isAdmin() && $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->isAdmin()),
                    Tables\Actions\BulkAction::make('updateRole')
                        ->label('Update Role')
                        ->icon('heroicon-o-shield-check')
                        ->form([
                            Forms\Components\Select::make('role')
                                ->label('New Role')
                                ->options([
                                    'admin' => 'Administrator',
                                    'manager' => 'Manager',
                                    'user' => 'User',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['role' => $data['role']]);
                                $record->syncRoles([$data['role']]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->visible(fn (): bool => auth()->user()->isAdmin()),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UserListsRelationManager::class,
            RelationManagers\ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email,
            'Role' => ucfirst($record->role),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(!auth()->user()->isAdmin(), function (Builder $query) {
                $query->where('id', auth()->id());
            });
    }

    public static function canCreate(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->isAdmin() || $record->id === auth()->id();
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->isAdmin() && $record->id !== auth()->id();
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->isAdmin();
    }
}