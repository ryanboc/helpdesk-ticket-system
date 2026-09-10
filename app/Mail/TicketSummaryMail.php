<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TicketSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $employee, public Collection $tickets, public string $frequency) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: ucfirst($this->frequency).' ticket summary');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-summary');
    }
}
