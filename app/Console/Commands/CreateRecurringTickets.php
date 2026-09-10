<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateRecurringTickets extends Command
{
    protected $signature = 'tickets:create-recurring';

    protected $description = 'Create the next due instance of recurring tickets.';

    public function handle(): int
    {
        Ticket::query()->where('is_recurring', true)->whereNotNull('recurrence_next_at')
            ->whereDate('recurrence_next_at', '<=', today())->orderBy('id')->each(function (Ticket $template): void {
                if ($template->recurrence_ends_at && $template->recurrence_next_at->gt($template->recurrence_ends_at)) {
                    return;
                }
                $dueDate = $template->recurrence_next_at->copy();
                Ticket::create([
                    'user_id' => $template->user_id, 'assigned_to_id' => $template->assigned_to_id,
                    'project_id' => $template->project_id, 'title' => $template->title, 'message' => $template->message,
                    'priority' => $template->priority, 'status' => 'open', 'deadline_date' => $dueDate,
                    'recurrence_source_id' => $template->id,
                ]);
                $template->update(['recurrence_next_at' => $this->nextDate($dueDate, $template->recurrence_frequency, $template->recurrence_interval)]);
                $this->line("Created recurring ticket from #{$template->id}");
            });

        return self::SUCCESS;
    }

    private function nextDate(Carbon $date, string $frequency, int $interval): Carbon
    {
        return match ($frequency) {
            'weekly' => $date->addWeeks($interval),
            'monthly' => $date->addMonthsNoOverflow($interval),
            default => $date->addDays($interval),
        };
    }
}
