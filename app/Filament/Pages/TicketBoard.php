<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use App\Models\Comment;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Facades\Blade;
use Filament\Notifications\Notification;

class TicketBoard extends KanbanBoard
{
    protected static string $model = Ticket::class;
    protected static string $recordView = 'filament.pages.kanban-card';
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Ticket Board';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false;

    public $ticketComments = [];

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
            ->with('assignedTo','project')
            ->when(! auth()->user()->is_admin, function ($query) {
                $query->where('user_id', auth()->id())
                      ->orWhere('assigned_to_id', auth()->id());
            })
            ->latest()
            ->get();
    }

    

    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        // 1. Keep your existing Logging
        \Illuminate\Support\Facades\Log::info("DRAG EVENT FIRED:", [
            'record_id' => $recordId,
            'new_status' => $status
        ]);

        $ticket = Ticket::find($recordId);

        if (!$ticket) {
            \Illuminate\Support\Facades\Log::error("TICKET NOT FOUND: ID " . $recordId);
            return;
        }

        try {
            
            $ticket->update(['status' => $status]);
            
            \Illuminate\Support\Facades\Log::info("UPDATE SUCCESSFUL: Ticket #{$recordId} -> {$status}");

            
            if ($ticket->assignedTo) {
                Notification::make()
                    ->title('Ticket Moved')
                    ->body("Ticket '{$ticket->title}' is now " . str($status)->headline())
                    ->icon('heroicon-o-arrow-path')
                    ->info()
                    ->sendToDatabase($ticket->assignedTo);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("UPDATE FAILED: " . $e->getMessage());
        }
    }

    public function recordClicked(int|string $recordId, array $data = []): void
    {
        
        $this->editModalRecordId = $recordId;

        
        $record = Ticket::find($recordId);

        
        if ($record) {
            
            $this->editModalFormState = [
                'assigned_to_id' => $record->assigned_to_id,
                'title'          => $record->title,
                'original_message' => $record->message,
                'status'         => $record->status,
                'priority'       => $record->priority,
            ];
        }

       
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
                    Forms\Components\RichEditor::make('message')
                        ->required()
                        ->label('Description & Screenshots')
                        ->fileAttachmentsDisk('public') // Store images in public folder
                        ->fileAttachmentsDirectory('ticket-images') // Subfolder name
                        ->fileAttachmentsVisibility('public'),
                    Forms\Components\Select::make('priority')
                        ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                        ->required(),
                    Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->required() // Optional: make it required if you want
                    ->columnSpanFull(),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    $data['status'] = 'open';
                    return $data;
                })
                ->after(function (Ticket $record) {
                    // Send notification to the assigned user (even if it's me)
                    if ($record->assignedTo) {
                        Notification::make()
                            ->title('New Ticket Assigned')
                            ->body("You have been assigned to '{$record->title}'")
                            ->success()
                            ->sendToDatabase($record->assignedTo);
                    }
                }),
        ];
    }

    protected function getEditModalFormSchema(null|int|string $recordId): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('assigned_to_id')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('priority')
                    ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options(['open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed'])
                    ->required(),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->label('Project')
                    ->searchable()
                    ->preload(),
            ]),

            
            Forms\Components\RichEditor::make('original_message')
                ->label('Original Issue')
                ->disabled() // Keep it read-only
                ->columnSpanFull(),
            
            

            Forms\Components\Section::make('Work Notes & History')
                ->schema([
                   Forms\Components\Placeholder::make('history')
                    ->hiddenLabel()
                    ->content(function () {
                        
                        $recordId = $this->editModalRecordId;
                        
                        
                        $comments = $recordId 
                            ? \App\Models\Comment::where('ticket_id', $recordId)->with('user')->latest()->get() 
                            : collect();

                       
                        return view('filament.pages.ticket-comments', ['comments' => $comments]);
                    }),
                

                Forms\Components\Textarea::make('new_comment')
                    ->label('Add a Note / Reply')
                    ->placeholder('Type your reply here...')
                    ->rows(3),
                ]),
        ];
    }

    // public function editModalFormSubmitted(): void
    // {
    //     $data = $this->editModalFormState;
    //     $record = Ticket::find($this->editModalRecordId);

    //     if ($record) {
            
    //         $record->update([
    //             'assigned_to_id' => $data['assigned_to_id'],
    //             'title'          => $data['title'],
    //             'status'         => $data['status'],
    //             'priority'       => $data['priority'],
    //         ]);

            
    //         if (!empty($data['new_comment'])) {
    //             Comment::create([
    //                 'ticket_id' => $record->id,
    //                 'user_id'   => auth()->id(),
    //                 'body'      => $data['new_comment'],
    //             ]);
    //         }
    //     }

    //     $this->dispatch('close-modal', id: 'kanban--edit-record-modal');
    //     $this->dispatch('refresh-kanban');
    // }

    public function editModalFormSubmitted(): void
    {
        $data = $this->editModalFormState;
        $record = Ticket::find($this->editModalRecordId);

        if ($record) {
            // 1. NEW: Capture old assignee ID before updating
            $oldAssignee = $record->assigned_to_id;

            // 2. Update the record
            $record->update([
                'assigned_to_id' => $data['assigned_to_id'],
                'title'          => $data['title'],
                'status'         => $data['status'],
                'priority'       => $data['priority'],
            ]);

            // 3. NEW: Notify if Assignment Changed
            if ($oldAssignee !== $data['assigned_to_id'] && $data['assigned_to_id']) {
                $newAssignee = \App\Models\User::find($data['assigned_to_id']);
                
                if ($newAssignee) {
                    Notification::make()
                        ->title('New Ticket Assigned')
                        ->body("You have been assigned to '{$record->title}'")
                        ->success()
                        ->sendToDatabase($newAssignee);
                }
            }

            // 4. Create Comment & Notify
            if (!empty($data['new_comment'])) {
                Comment::create([
                    'ticket_id' => $record->id,
                    'user_id'   => auth()->id(),
                    'body'      => $data['new_comment'],
                ]);

                // Notify the assignee (if it's not me)
                if ($record->assignedTo && $record->assigned_to_id !== auth()->id()) {
                    Notification::make()
                        ->title('New Comment')
                        ->body(auth()->user()->name . " commented on '{$record->title}'")
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->warning()
                        ->sendToDatabase($record->assignedTo);
                }
            }
        }

        $this->dispatch('close-modal', id: 'kanban--edit-record-modal');
        $this->dispatch('refresh-kanban');

        // 5. NEW: Success Toast for the user who clicked Save
        // Notification::make()
        //     ->title('Saved successfully')
        //     ->success()
        //     ->send();
        Notification::make()
            ->title('System Test')
            ->body('If you see this, the Bell is working!')
            ->success()
            ->sendToDatabase(auth()->user()) // <--- Force send to YOU
            ->send(); // <--- Also show the Toast
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
            'ghostClass' => 'sortable-ghost', 
            'dragClass'  => 'sortable-drag',  
            'animation'  => 150,
        ];
    }
}