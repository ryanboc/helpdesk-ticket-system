<div 
    id="{{ $record->id }}"
    wire:sortable.item="{{ $record->id }}" 
    wire:key="{{ $record->id }}"
    wire:click="recordClicked('{{ $record->id }}')"
    class="group relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 cursor-pointer hover:shadow-md hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-200"
>
    <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-lg
        {{ $record->priority === 'high' ? 'bg-red-500' : ($record->priority === 'medium' ? 'bg-yellow-500' : 'bg-green-500') }}">
    </div>

    @if($record->project)
        @php
            // Define the specific color styles for each option
            $colors = [
                'gray'   => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                'blue'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                'green'  => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300',
                'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                'red'    => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
                'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300',
            ];
            
            // Default to gray if the color isn't found
            $badgeClass = $colors[$record->project->color] ?? $colors['gray'];
        @endphp

        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ring-opacity-10 {{ $badgeClass }}">
            {{ $record->project->name }}
        </span>
    @endif

    <div class="flex justify-between items-center mb-2 pl-2">
        <span class="text-[10px] font-mono font-bold text-gray-400 group-hover:text-blue-500 transition-colors">
            TK-{{ $record->id }}
        </span>
        
        @if($record->priority === 'high')
            <span class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-[10px] font-bold border border-red-100">
                High
            </span>
        @elseif($record->priority === 'medium')
            <span class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-600 text-[10px] font-bold border border-yellow-100">
                Med
            </span>
        @else
             <span class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 text-green-600 text-[10px] font-bold border border-green-100">
                Low
            </span>
        @endif
    </div>

    <div class="mb-4 pl-2">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 leading-snug">
            {{ $record->title }}
        </h3>
    </div>

    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700 pl-2">
        <div class="flex items-center gap-2">
            @if($record->assignedTo)
                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-[9px] font-bold shadow-sm">
                    {{ substr($record->assignedTo->name, 0, 1) }}
                </div>
                <span class="text-xs text-gray-600 dark:text-gray-300 font-medium truncate max-w-[80px]">
                    {{ explode(' ', $record->assignedTo->name)[0] }}
                </span>
            @else
                <div class="w-6 h-6 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center text-gray-400 text-[10px]">
                    ?
                </div>
                <span class="text-xs text-gray-400 italic">Unassigned</span>
            @endif
        </div>
        <span class="text-[10px] text-gray-400 font-medium">
            {{ $record->created_at->format('M d') }}
        </span>
    </div>
</div>