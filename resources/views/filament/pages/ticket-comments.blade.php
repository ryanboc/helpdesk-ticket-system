<div class="space-y-4 max-h-60 overflow-y-auto p-2 border rounded bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700">
    
    <style>
        /* YOUR BUBBLES (Right): Modern Indigo */
        .bubble-mine {
            background-color: #4f46e5 !important; /* Indigo-600 */
            color: #ffffff !important;
            border: 1px solid #4338ca !important;
        }
        
        /* THEIR BUBBLES (Left): Light Mode - Warm Gray */
        .bubble-theirs {
            background-color: #f3f4f6 !important; /* Gray-100 */
            color: #111827 !important; /* Gray-900 (High Contrast) */
            border: 1px solid #e5e7eb !important;
        }

        /* THEIR BUBBLES (Left): Dark Mode - Slate Gray */
        .dark .bubble-theirs {
            background-color: #374151 !important; /* Gray-700 */
            color: #f9fafb !important; /* Gray-50 */
            border-color: #4b5563 !important;
        }
    </style>

    @forelse($comments as $comment)
        @php $mine = $comment->user_id === auth()->id(); @endphp

        <div class="flex gap-3 {{ $mine ? 'flex-row-reverse' : '' }}">
            
            <div class="w-8 h-8 rounded-full border flex items-center justify-center text-xs font-bold shrink-0
                bg-gray-100 border-gray-200 text-gray-600 
                dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                {{ substr($comment->user->name ?? 'U', 0, 1) }}
            </div>

            <div class="max-w-[85%]">
                <div class="text-[10px] mb-1 {{ $mine ? 'text-right' : '' }} text-gray-500 dark:text-gray-400">
                    {{ $comment->user->name ?? 'Unknown' }} • {{ $comment->created_at->diffForHumans() }}
                </div>

                <div class="p-3 rounded-lg text-sm shadow-sm
                    {{ $mine 
                        ? 'bubble-mine rounded-tr-none' 
                        : 'bubble-theirs rounded-tl-none' }}">
                    {{ $comment->body }}
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-sm py-4 text-gray-500 dark:text-gray-400">
            No notes yet. Be the first to add one!
        </div>
    @endforelse
</div>