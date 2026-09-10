<?php

namespace App\Console\Commands;

use App\Mail\ManagerTicketReportMail;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendManagerTicketReport extends Command
{
    protected $signature = 'tickets:send-manager-report
                            {email : Manager email address}
                            {period=week : Report period: week or month (ignored when --from and --to are supplied)}
                            {--from= : Start date for a custom report range (YYYY-MM-DD)}
                            {--to= : End date for a custom report range (YYYY-MM-DD)}';

    protected $description = 'Email a manager a report of completed, open, and in-progress tickets.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $period = $this->argument('period');
        $from = $this->option('from');
        $to = $this->option('to');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please provide a valid manager email address.');

            return self::FAILURE;
        }

        if (($from && ! $to) || ($to && ! $from)) {
            $this->error('Use --from and --to together.');

            return self::FAILURE;
        }

        if (! $from && ! $to && ! in_array($period, ['week', 'month'], true)) {
            $this->error('Period must be either week or month.');

            return self::FAILURE;
        }

        try {
            [$period, $periodStart, $periodEnd] = $from && $to
                ? ['custom range', Carbon::createFromFormat('!Y-m-d', $from)->startOfDay(), Carbon::createFromFormat('!Y-m-d', $to)->endOfDay()]
                : ($period === 'week'
                    ? ['week', now()->startOfWeek(), now()->endOfWeek()]
                    : ['month', now()->startOfMonth(), now()->endOfMonth()]);
        } catch (\Exception) {
            $this->error('Dates must use the YYYY-MM-DD format.');

            return self::FAILURE;
        }

        if ($periodStart->gt($periodEnd)) {
            $this->error('The --from date must be on or before the --to date.');

            return self::FAILURE;
        }

        $ticketQuery = Ticket::query()->with(['assignedTo', 'project']);

        $finishedTickets = (clone $ticketQuery)
            ->where('status', 'finished')
            ->whereBetween('updated_at', [$periodStart, $periodEnd])
            ->latest('updated_at')
            ->get();

        $openTickets = (clone $ticketQuery)
            ->where('status', 'open')
            ->orderByRaw('deadline_date is null, deadline_date')
            ->get();

        $inProgressTickets = (clone $ticketQuery)
            ->where('status', 'in_progress')
            ->orderByRaw('deadline_date is null, deadline_date')
            ->get();

        Mail::to($email)->send(new ManagerTicketReportMail(
            $finishedTickets,
            $openTickets,
            $inProgressTickets,
            $period,
            $periodStart,
            $periodEnd,
        ));

        $this->info("{$period} manager report sent to {$email}.");

        return self::SUCCESS;
    }
}
