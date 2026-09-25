<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($frequency) }} Ticket Summary</title>
</head>
<body style="margin:0;background:#f4f7fb;color:#172033;font-family:Arial,Helvetica,sans-serif;line-height:1.5">
    <div style="padding:32px 16px">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e5eaf2;border-radius:16px;overflow:hidden">
            <tr>
                <td style="padding:32px;background:#172554;color:#ffffff">
                    <div style="font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:#bfdbfe">Helpdesk</div>
                    <h1 style="margin:8px 0 0;font-size:28px;line-height:1.2">{{ ucfirst($frequency) }} Ticket Summary</h1>
                    <p style="margin:10px 0 0;color:#dbeafe;font-size:15px">A clear view of what needs your attention.</p>
                </td>
            </tr>
            <tr>
                <td style="padding:32px">
                    <p style="margin:0 0 20px;font-size:16px">Hello {{ $employee->name }},</p>
                    <p style="margin:0 0 24px;color:#526078">Here are your active tickets and the latest updates that matter.</p>

                    @if ($tickets->isEmpty())
                        <div style="padding:20px;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4;color:#166534">
                            <strong>You’re all caught up.</strong><br>
                            There are no active tickets requiring your attention right now.
                        </div>
                    @else
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #e5eaf2;border-radius:10px;overflow:hidden">
                            <thead>
                                <tr style="background:#f8fafc;text-align:left;color:#526078;font-size:12px;text-transform:uppercase;letter-spacing:.04em">
                                    <th style="padding:12px">Ticket</th>
                                    <th style="padding:12px">Status</th>
                                    <th style="padding:12px">Priority</th>
                                    <th style="padding:12px">Deadline</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $ticket)
                                    <tr style="border-top:1px solid #e5eaf2;font-size:14px">
                                        <td style="padding:14px 12px;color:#172033"><strong>#{{ $ticket->id }}</strong><br>{{ $ticket->title }}</td>
                                        <td style="padding:14px 12px">{{ str($ticket->status)->replace('_', ' ')->headline() }}</td>
                                        <td style="padding:14px 12px">{{ ucfirst($ticket->priority) }}</td>
                                        <td style="padding:14px 12px;white-space:nowrap">{{ $ticket->deadline_date?->format('D, j M Y') ?? 'No deadline' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <div style="margin-top:28px;text-align:center">
                        <a href="https://helpdesk/" style="display:inline-block;padding:13px 22px;border-radius:9px;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:bold">Open Helpdesk</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding:20px 32px;background:#f8fafc;color:#718096;font-size:12px;text-align:center">
                    This is an automated ticket summary from Helpdesk.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
