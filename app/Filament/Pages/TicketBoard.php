<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class TicketBoard extends KanbanBoard
{
    protected static string $model = Ticket::class;
    protected static string $recordView = 'filament.pages.kanban-card';
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Ticket Board';
    protected static ?int $navigationSort = 2;

    protected function statuses(): \Illuminate\Support\Collection
    {
        return collect([
            ['id' => 'open', 'title' => 'Open'],
            ['id' => 'in_progress', 'title' => 'In Progress'],
            ['id' => 'closed', 'title' => 'Closed'],
        ]);
    }

    protected function records(): \Illuminate\Support\Collection
    {
        return Ticket::query()
            ->with('assignedTo')
            ->when(! auth()->user()->is_admin, function ($query) {
                $query->where('user_id', auth()->id())
                      ->orWhere('assigned_to_id', auth()->id());
            })
            ->latest()
            ->get();
    }

    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        // 1. Log what is happening (Check storage/logs/laravel.log)
        \Illuminate\Support\Facades\Log::info("DRAG EVENT FIRED:", [
            'record_id' => $recordId,
            'new_status' => $status
        ]);

        // 2. Find the ticket
        $ticket = Ticket::find($recordId);

        if (!$ticket) {
            \Illuminate\Support\Facades\Log::error("TICKET NOT FOUND: ID " . $recordId);
            return;
        }

        // 3. Try to update and catch errors
        try {
            $ticket->update(['status' => $status]);
            \Illuminate\Support\Facades\Log::info("UPDATE SUCCESSFUL: Ticket #{$recordId} -> {$status}");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("UPDATE FAILED: " . $e->getMessage());
        }
    }

    public function recordClicked(int|string $recordId, array $data = []): void
    {
        // 1. Set the ID
        $this->editModalRecordId = $recordId;

        // 2. Find the Ticket
        $record = Ticket::find($recordId);

        // 3. FORCE FILL the form state (The "Brute Force" Fix)
        if ($record) {
            // We fill the specific array that the form reads from
            $this->editModalFormState = [
                'assigned_to_id' => $record->assigned_to_id,
                'title'          => $record->title,
                'message'        => $record->message,
                'status'         => $record->status,
                'priority'       => $record->priority,
            ];
        }

        // 4. Open the modal
        $this->dispatch('open-modal', id: 'kanban--edit-record-modal');
    }

    protected function getEditModalRecordData(null|int|string $recordId, array $data): array
    {
        return Ticket::find($recordId)?->toArray() ?? [];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->model(Ticket::class)
                ->label('New Ticket')
                ->form([
                    Forms\Components\Select::make('assigned_to_id')
                        ->relationship('assignedTo', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('title')->required(),
                    Forms\Components\Textarea::make('message')->required(),
                    Forms\Components\Select::make('priority')
                        ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                        ->required(),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    $data['status'] = 'open';
                    return $data;
                }),
        ];
    }

    protected function getEditModalFormSchema(null|int|string $recordId): array
    {
        return [
            Forms\Components\Select::make('assigned_to_id')
                ->relationship('assignedTo', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Textarea::make('message')
                ->rows(4)
                ->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->options(['open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed'])
                ->required(),
            Forms\Components\Select::make('priority')
                ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                ->required(),
        ];
    }

    protected function getEditModalActions(null|int|string $recordId): array
    {
        return [
            DeleteAction::make()
                ->record(Ticket::find($recordId))
                ->requiresConfirmation(),
        ];
    }

    protected function sortableOptions(): array
    {
        return [
            'ghostClass' => 'sortable-ghost', // The empty gray placeholder
            'dragClass'  => 'sortable-drag',  // The tilted blue card
            'animation'  => 150,
        ];
    }
}