<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket'; // Changed to ticket icon

    protected static ?string $navigationLabel = 'Support Tickets';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            // 1. The "Assignee" (Agent working on the ticket)
            Forms\Components\Select::make('assigned_to_id')
                ->label('Assign to Employee')
                ->relationship('assignedTo', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Unassigned'),

            // 2. The "Creator" (Customer) - THIS WAS MISSING
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->default(auth()->id())
                ->searchable()
                ->preload()
                ->required()
                // Disable for non-admins so they can't pretend to be someone else
                ->disabled(fn () => ! auth()->user()->is_admin)
                // IMPORTANT: Send the data even if disabled!
                ->dehydrated(),

            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(), // Optional: Makes title take up full width

            Forms\Components\Textarea::make('message')
                ->required()
                ->columnSpanFull(),

            Forms\Components\Select::make('priority')
                ->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                ])
                ->required(),

            Forms\Components\Select::make('status')
                ->options([
                    'open' => 'Open',
                    'in_progress' => 'In Progress',
                    'closed' => 'Closed',
                ])
                ->default('open')
                ->hiddenOn('create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned Agent')
                    ->placeholder('Unassigned')
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'gray',
                        'in_progress' => 'info',
                        'closed' => 'success',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority'),
                Tables\Filters\SelectFilter::make('status'),
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

    public static function getEloquentQuery(): Builder
    {
        // If the logged-in user is NOT an admin, only show their own tickets
        if (! auth()->user()->is_admin) {
            return parent::getEloquentQuery()->where('user_id', auth()->id());
        }

        return parent::getEloquentQuery();
    }

    public static function getRelations(): array
    {
        return [
            // This is where your comments/replies will appear
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}