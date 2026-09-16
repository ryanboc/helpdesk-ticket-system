<x-filament-panels::page>
    <div class="max-w-3xl">
        <form wire:submit="downloadReport" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-m-arrow-down-tray">
                Download PDF report
            </x-filament::button>
        </form>
    </div>
</x-filament-panels::page>
