<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Ticket;
use App\Models\Project;
use Filament\Resources\Pages\Page;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Illuminate\Support\Collection;

class ProjectTicketBoard extends KanbanBoard
{
  
    protected static string $resource = ProjectResource::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static string $model = Ticket::class;
    protected static string $recordView = 'filament.pages.kanban-card';
    
    public function getTitle(): string
    {
        return $this->record->name . ' - Ticket Board';
    }

    public $record;

    // 4. Capture the Project ID from the URL
    public function mount(): void
    {
        // Get the 'record' (ID) from the URL parameter
        $recordId = request()->route()->parameter('record');
        $this->record = Project::findOrFail($recordId);
        
        parent::mount();
    }

    protected function statuses(): Collection
    {
        return collect([
            ['id' => 'open', 'title' => 'Open'],
            ['id' => 'in_progress', 'title' => 'In Progress'],
            ['id' => 'closed', 'title' => 'Closed'],
        ]);
    }

    protected function records(): Collection
    {
        // 5. FILTER: Only show tickets for THIS project
        return Ticket::query()
            ->where('project_id', $this->record->id)
            ->with(['assignedTo'])
            ->latest()
            ->get();
    }

    // 6. Simplified "New Ticket" (No need to select Project)
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
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('ticket-images')
                        ->fileAttachmentsVisibility('public'),
                    Forms\Components\Select::make('priority')
                        ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                        ->required(),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    $data['status'] = 'open';
                    $data['project_id'] = $this->record->id; // Auto-assign Project
                    return $data;
                }),
        ];
    }
    
    // 7. Status Change Logic (Drag and Drop)
    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        Ticket::find($recordId)?->update(['status' => $status]);
    }

    public static function route(string $path): \Filament\Resources\Pages\PageRegistration
    {
        return new \Filament\Resources\Pages\PageRegistration(
            page: static::class,
            route: fn (\Filament\Panel $panel) => \Illuminate\Support\Facades\Route::get($path, static::class)
                ->middleware(static::getRouteMiddleware($panel))
                ->withoutMiddleware(static::getWithoutRouteMiddleware($panel)),
        );
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
            ]),

            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),

            // The Rich Text "Original Issue" display
            Forms\Components\RichEditor::make('message')
                ->label('Original Issue')
                ->disabled() // Read-only so history isn't changed
                ->columnSpanFull(),

            // Work Notes & Comments Section
            Forms\Components\Section::make('Work Notes & History')
                ->schema([
                   Forms\Components\Placeholder::make('history')
                    ->hiddenLabel()
                    ->content(function () use ($recordId) {
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

    public function editModalFormSubmitted(): void
    {
        // 1. Get the data from the class property instead of the argument
        $data = $this->editModalFormState;

        // 2. Get the ticket
        $ticket = Ticket::find($this->editModalRecordId);
        
        // 3. Update standard fields (title, priority, assigned_to)
        // We use $data here just like before
        $ticket->update(collect($data)->except(['new_comment', 'history'])->toArray());

        // 4. If there is a new comment, save it
        if (! empty($data['new_comment'])) {
            \App\Models\Comment::create([
                'ticket_id' => $ticket->id,
                'user_id'   => auth()->id(),
                'body'      => $data['new_comment'],
            ]);

            // Optional: Send notification to the assigned user
            if ($ticket->assignedTo && $ticket->assignedTo->id !== auth()->id()) {
                \Filament\Notifications\Notification::make()
                    ->title('New Comment on Ticket')
                    ->body(auth()->user()->name . ' commented on ' . $ticket->title)
                    ->success()
                    ->sendToDatabase($ticket->assignedTo);
            }
        }
        
        // 5. Close the modal and refresh
        $this->record = $ticket->project; // Keep the project context
    }
}