<?php

namespace App\Console\Commands;

use App\Mail\TicketSummaryMail;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendTicketSummaries extends Command
{
    protected $signature = 'tickets:send-summaries {frequency : daily or weekly}';

    protected $description = 'Email opted-in employees their active ticket summary.';

    public function handle(): int
    {
        $frequency = $this->argument('frequency');
        if (! in_array($frequency, ['daily', 'weekly'], true)) {
            $this->error('Frequency must be daily or weekly.');

            return self::FAILURE;
        }

        User::query()->where('receives_ticket_summaries', true)->whereIn('ticket_summary_frequency', [$frequency, 'both'])
            ->each(function (User $employee) use ($frequency): void {
                $since = $frequency === 'daily' ? Carbon::now()->subDay() : Carbon::now()->subWeek();
                $tickets = Ticket::query()->where('assigned_to_id', $employee->id)
                    ->where(function ($query) use ($since) {
                        $query->whereNotIn('status', ['finished', 'closed'])
                            ->orWhere(function ($query) use ($since) {
                                $query->whereIn('status', ['finished', 'closed'])->where('updated_at', '>=', $since);
                            });
                    })
                    ->orderByRaw('deadline_date is null, deadline_date')->orderByDesc('priority')->get();
                Mail::to($employee->email)->send(new TicketSummaryMail($employee, $tickets, $frequency));
                $this->line("Sent {$frequency} summary to {$employee->email}");
            });

        return self::SUCCESS;
    }
}
