<!doctype html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f3f4f6;font-family:Arial,sans-serif;color:#1f2937;line-height:1.5">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center">
<table role="presentation" width="680" cellspacing="0" cellpadding="0" style="max-width:680px;background:#fff;border-collapse:collapse">
<tr><td style="padding:28px 32px;background:#111827;color:#fff"><h1 style="margin:0;font-size:24px">Helpdesk ticket report</h1><p style="margin:6px 0 0;color:#d1d5db">{{ ucfirst($period) }}: {{ $periodStart->format('j M Y') }} – {{ $periodEnd->format('j M Y') }}</p></td></tr>
<tr><td style="padding:28px 32px">
<table role="presentation" width="100%" cellspacing="8" cellpadding="0"><tr>
<td width="33%" style="padding:14px;background:#dcfce7"><strong style="font-size:24px">{{ $finishedTickets->count() }}</strong><br>Finished</td>
<td width="33%" style="padding:14px;background:#dbeafe"><strong style="font-size:24px">{{ $openTickets->count() }}</strong><br>Open</td>
<td width="33%" style="padding:14px;background:#fef3c7"><strong style="font-size:24px">{{ $inProgressTickets->count() }}</strong><br>In progress</td>
</tr></table>

@foreach ([
    ['Finished tickets', $finishedTickets, '#166534', 'Finished during the selected period'],
    ['Open tickets', $openTickets, '#1d4ed8', 'Current backlog'],
    ['In-progress tickets', $inProgressTickets, '#92400e', 'Currently being worked on'],
] as [$heading, $tickets, $colour, $description])
<h2 style="margin:30px 0 4px;color:{{ $colour }};font-size:18px">{{ $heading }} ({{ $tickets->count() }})</h2>
<p style="margin:0 0 10px;color:#6b7280;font-size:13px">{{ $description }}</p>
@if ($tickets->isEmpty())
<p style="padding:12px;background:#f9fafb;margin:0">None.</p>
@else
<table width="100%" cellspacing="0" cellpadding="8" style="border-collapse:collapse;font-size:14px"><thead><tr style="background:#f3f4f6;text-align:left"><th>Ticket</th><th>Assigned to</th><th>Project</th><th>Priority</th><th>Deadline</th></tr></thead><tbody>
@foreach ($tickets as $ticket)
<tr style="border-bottom:1px solid #e5e7eb"><td>#{{ $ticket->id }} — {{ $ticket->title }}</td><td>{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</td><td>{{ $ticket->project?->name ?? '—' }}</td><td>{{ ucfirst($ticket->priority) }}</td><td>{{ $ticket->deadline_date?->format('j M Y') ?? 'No deadline' }}</td></tr>
@endforeach
</tbody></table>
@endif
@endforeach
</td></tr>
</table>
</td></tr></table>
</body>
</html>
