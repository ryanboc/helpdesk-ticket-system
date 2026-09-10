<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Employees';

    protected static ?string $pluralModelLabel = 'Employees';

    protected static ?string $modelLabel = 'Employee';

    protected static ?string $slug = 'employees';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                // Password handling is tricky!
                // We only want to require it on creation,
                // and we need to hash it before saving.
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state)) // Only save if user typed something
                    ->required(fn (string $context): bool => $context === 'create'),

                Forms\Components\Toggle::make('receives_ticket_summaries')
                    ->label('Receive ticket-summary emails')
                    ->default(true)
                    ->live(),
                Forms\Components\Select::make('ticket_summary_frequency')
                    ->label('Summary frequency')
                    ->options(['daily' => 'Daily', 'weekly' => 'Weekly', 'both' => 'Daily and weekly'])
                    ->default('daily')
                    ->visible(fn (Forms\Get $get) => $get('receives_ticket_summaries')),
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Only show this menu item if the user is an Admin
        return auth()->user()?->is_admin ?? false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ticket_summary_frequency')
                    ->label('Email summary')
                    ->badge()
                    ->formatStateUsing(fn (?string $state, User $record) => $record->receives_ticket_summaries ? ucfirst($state ?? 'daily') : 'Off'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
