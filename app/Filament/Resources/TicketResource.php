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
    // protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket'; // Changed to ticket icon

    // protected static ?string $navigationLabel = 'Support Tickets';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

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
                    ->default('No additional details provided.')
                    ->columnSpanFull(),

                Forms\Components\Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ])
                    ->default('medium')
                    ->required(),

                Forms\Components\DatePicker::make('deadline_date')
                    ->label('Deadline')
                    ->native(false)
                    ->helperText('Optional — appears in employee summaries.'),

                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'closed' => 'Closed',
                        'finished' => 'Finished',
                    ])
                    ->default('open')
                    ->hiddenOn('create'),

                Forms\Components\Section::make('Recurring ticket')
                    ->description('Use this as a template for work that repeats, such as daily database backups or certificate renewals.')
                    ->compact()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Toggle::make('is_recurring')->label('Create recurring tickets')->live(),
                        Forms\Components\Select::make('recurrence_frequency')->label('Repeats')->options([
                            'daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly',
                        ])->default('daily')->required(fn (Forms\Get $get) => $get('is_recurring'))->visible(fn (Forms\Get $get) => $get('is_recurring')),
                        Forms\Components\TextInput::make('recurrence_interval')->label('Every')->numeric()->minValue(1)->default(1)->required(fn (Forms\Get $get) => $get('is_recurring'))->visible(fn (Forms\Get $get) => $get('is_recurring')),
                        Forms\Components\DatePicker::make('recurrence_next_at')->label('First ticket date')->default(today())->required(fn (Forms\Get $get) => $get('is_recurring'))->visible(fn (Forms\Get $get) => $get('is_recurring')),
                        Forms\Components\DatePicker::make('recurrence_ends_at')->label('Stop after')->visible(fn (Forms\Get $get) => $get('is_recurring')),
                    ])->columns(2)->columnSpanFull(),
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
                        'finished' => 'success',
                    }),

                Tables\Columns\TextColumn::make('deadline_date')
                    ->label('Deadline')
                    ->date()
                    ->placeholder('—')
                    ->color(fn (Ticket $record): string => $record->deadline_date?->isPast() && ! in_array($record->status, ['closed', 'finished']) ? 'danger' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\Filter::make('overdue')->query(fn (Builder $query): Builder => $query->whereDate('deadline_date', '<', today())->whereNotIn('status', ['closed', 'finished'])),
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
