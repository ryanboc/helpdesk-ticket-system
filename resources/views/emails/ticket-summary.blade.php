<!doctype html>
<html lang="en"><body style="font-family:Arial,sans-serif;color:#1f2937;line-height:1.5">
<h2>{{ ucfirst($frequency) }} ticket summary</h2>
<p>Hello {{ $employee->name }}, here are your active tickets.</p>
@if ($tickets->isEmpty())
<p>You have no active tickets right now. Great work.</p>
@else
<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse"><thead><tr style="background:#f3f4f6;text-align:left"><th>Ticket</th><th>Status</th><th>Priority</th><th>Deadline</th></tr></thead><tbody>
@foreach ($tickets as $ticket)
<tr style="border-bottom:1px solid #e5e7eb"><td>#{{ $ticket->id }} — {{ $ticket->title }}</td><td>{{ str($ticket->status)->replace('_', ' ')->headline() }}</td><td>{{ ucfirst($ticket->priority) }}</td><td>{{ $ticket->deadline_date?->format('D, j M Y') ?? 'No deadline' }}</td></tr>
@endforeach
</tbody></table>
@endif
<p style="margin-top:20px">Open the helpdesk to review or update your tickets.</p>
</body></html>
