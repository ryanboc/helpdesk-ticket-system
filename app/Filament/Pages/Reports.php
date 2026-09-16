<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Symfony\Component\Process\Process;

class Reports extends Page implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.reports';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => today()->startOfMonth()->toDateString(),
            'end_date' => today()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('start_date')
                    ->label('From')
                    ->native(false)
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('To')
                    ->native(false)
                    ->required()
                    ->afterOrEqual('start_date'),
            ])
            ->statePath('data');
    }

    public function downloadReport()
    {
        $data = $this->form->getState();
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

        if ($startDate->greaterThan($endDate)) {
            $this->addError('data.end_date', 'The end date must be on or after the start date.');

            return null;
        }

        $tickets = Ticket::query()
            ->with(['user', 'assignedTo', 'project'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when(! auth()->user()->is_admin, function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('user_id', auth()->id())
                        ->orWhere('assigned_to_id', auth()->id());
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $html = view('filament.pages.ticket-report-pdf', [
            'tickets' => $tickets,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
        ])->render();

        $tempDirectory = storage_path('app/report-pdfs');
        if (! is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0755, true);
        }

        $runtimeDirectory = $tempDirectory.'/runtime';
        if (! is_dir($runtimeDirectory)) {
            mkdir($runtimeDirectory, 0700, true);
        }

        $inputPath = tempnam($tempDirectory, 'report-html-');
        $htmlInputPath = $inputPath.'.html';
        rename($inputPath, $htmlInputPath);
        $inputPath = $htmlInputPath;
        $outputPath = tempnam($tempDirectory, 'report-pdf-');
        file_put_contents($inputPath, $html);
        unlink($outputPath);

        try {
            $process = new Process([
                'google-chrome',
                '--headless',
                '--no-sandbox',
                '--disable-gpu',
                '--disable-dev-shm-usage',
                '--no-pdf-header-footer',
                '--user-data-dir='.$runtimeDirectory.'/chrome-profile',
                '--print-to-pdf='.$outputPath,
                'file://'.$inputPath,
            ]);
            $process->setEnv([
                'HOME' => $tempDirectory,
                'XDG_RUNTIME_DIR' => $runtimeDirectory,
            ]);
            $process->setTimeout(30);
            $process->mustRun();

            return response()->download(
                $outputPath,
                sprintf('ticket-report-%s-to-%s.pdf', $startDate->toDateString(), $endDate->toDateString()),
                ['Content-Type' => 'application/pdf'],
            )->deleteFileAfterSend(true);
        } finally {
            if (is_file($inputPath)) {
                unlink($inputPath);
            }
        }
    }
}
