<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class ManagerTicketReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $finishedTickets,
        public Collection $openTickets,
        public Collection $inProgressTickets,
        public string $period,
        public Carbon $periodStart,
        public Carbon $periodEnd,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Helpdesk ticket report — '.ucfirst($this->period).' ending '.$this->periodEnd->format('j M Y'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.manager-ticket-report');
    }
}
