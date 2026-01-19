<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class TicketBoard extends KanbanBoard
{
    // Strict string type (No "?" allowed here)
    protected static string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationLabel = 'Ticket Board';

    protected static ?int $navigationSort = 2;

    protected function statuses(): \Illuminate\Support\Collection
    {
        return collect([
            [
                'id' => 'open',
                'title' => 'Open',
            ],
            [
                'id' => 'in_progress',
                'title' => 'In Progress',
            ],
            [
                'id' => 'closed',
                'title' => 'Closed',
            ],
        ]);
    }

    protected function records(): \Illuminate\Support\Collection
    {
        $query = Ticket::query();

        // Security: If not admin, only show my own tickets
        if (! auth()->user()->is_admin) {
            $query->where('user_id', auth()->id());
        }

        return $query->latest()->get();
    }

    // Handles the drag-and-drop logic
    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        Ticket::find($recordId)->update(['status' => $status]);
    }

    // The "New Ticket" button at the top right
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->model(Ticket::class)
                ->label('New Ticket')
                ->form([
                    Forms\Components\Select::make('assigned_to_id')
                        ->label('Assign to Employee')
                        ->relationship('assignedTo', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Unassigned'),

                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('message')
                        ->required(),

                    Forms\Components\Select::make('priority')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                        ])
                        ->required(),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    $data['status'] = 'open';
                    return $data;
                }),
        ];
    }

    // The popup that appears when you click a card
    protected function getEditModalFormSchema(null|int|string $recordId): array
    {
        return [
            Forms\Components\Select::make('assigned_to_id')
                ->label('Assign to Employee')
                ->relationship('assignedTo', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Unassigned'),

            Forms\Components\TextInput::make('title')
                ->required(),
            
            Forms\Components\Textarea::make('message')
            ->label('Description')
            ->required()
            ->rows(4)             
            ->columnSpanFull(),   

            Forms\Components\Select::make('status')
                ->options([
                    'open' => 'Open',
                    'in_progress' => 'In Progress',
                    'closed' => 'Closed',
                ])
                ->required(),

            Forms\Components\Select::make('priority')
                ->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                ])
                ->required(),
        ];
    }
    
    // Optional: If you want clicking the card to go to the full page instead of the modal,
    // uncomment this method. (But the modal above is usually better for quick edits).
    /*
    protected function getRecordUrl($record): string
    {
        return route('filament.admin.resources.tickets.edit', $record);
    }
    */
}