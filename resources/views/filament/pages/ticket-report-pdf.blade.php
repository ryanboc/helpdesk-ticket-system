<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Report</title>
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        body { color: #172033; font-family: Arial, sans-serif; font-size: 10px; margin: 0; }
        .header { border-bottom: 3px solid #f59e0b; padding-bottom: 14px; }
        h1 { font-size: 24px; margin: 0 0 5px; }
        .muted { color: #667085; }
        .summary { display: table; margin: 18px 0; width: 100%; }
        .stat { background: #f4f6f8; border-radius: 5px; display: table-cell; padding: 11px; width: 25%; }
        .stat + .stat { border-left: 6px solid white; }
        .stat-value { display: block; font-size: 18px; font-weight: bold; margin-bottom: 3px; }
        .stat-label { color: #667085; font-size: 9px; text-transform: uppercase; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #172033; color: white; font-size: 9px; padding: 8px 6px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #e5e7eb; padding: 8px 6px; vertical-align: top; }
        tr { page-break-inside: avoid; }
        .title { font-weight: bold; }
        .badge { border-radius: 3px; display: inline-block; font-size: 8px; padding: 3px 5px; }
        .open { background: #fef3c7; color: #92400e; }
        .in_progress { background: #dbeafe; color: #1e40af; }
        .finished, .closed { background: #dcfce7; color: #166534; }
        .high { color: #b91c1c; font-weight: bold; }
        .medium { color: #a16207; font-weight: bold; }
        .low { color: #166534; }
        .empty { border: 1px dashed #cbd5e1; color: #667085; padding: 24px; text-align: center; }
        footer { color: #98a2b3; font-size: 8px; margin-top: 18px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ticket Report</h1>
        <div class="muted">{{ $startDate->format('j M Y') }} - {{ $endDate->format('j M Y') }}</div>
    </div>

    <div class="summary">
        <div class="stat"><span class="stat-value">{{ $tickets->count() }}</span><span class="stat-label">Total tickets</span></div>
        <div class="stat"><span class="stat-value">{{ $tickets->where('status', 'open')->count() }}</span><span class="stat-label">Open</span></div>
        <div class="stat"><span class="stat-value">{{ $tickets->where('status', 'in_progress')->count() }}</span><span class="stat-label">In progress</span></div>
        <div class="stat"><span class="stat-value">{{ $tickets->whereIn('status', ['finished', 'closed'])->count() }}</span><span class="stat-label">Completed</span></div>
    </div>

    @if ($tickets->isEmpty())
        <div class="empty">No tickets were created during this date range.</div>
    @else
        <table>
            <thead><tr><th style="width: 33%">Ticket</th><th>Customer</th><th>Assigned to</th><th>Status</th><th>Priority</th><th>Created</th></tr></thead>
            <tbody>
            @foreach ($tickets as $ticket)
                <tr>
                    <td><div class="title">#{{ $ticket->id }} - {{ $ticket->title }}</div></td>
                    <td>{{ $ticket->user?->name ?? '-' }}</td>
                    <td>{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</td>
                    <td><span class="badge {{ $ticket->status }}">{{ str($ticket->status)->replace('_', ' ')->headline() }}</span></td>
                    <td class="{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</td>
                    <td>{{ $ticket->created_at->format('j M Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <footer>Generated {{ $generatedAt->format('j M Y, g:i a') }}.</footer>
</body>
</html>
