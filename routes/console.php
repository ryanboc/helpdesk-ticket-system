<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tickets:create-recurring')->dailyAt('00:05')->withoutOverlapping();
Schedule::command('tickets:send-summaries daily')
    ->dailyAt('10:05')
    ->timezone('Australia/Brisbane')
    ->withoutOverlapping();
Schedule::command('tickets:send-summaries weekly')->weeklyOn(1, '09:00')->withoutOverlapping();

if (filled(config('helpdesk.manager_report_email'))) {
    Schedule::command('tickets:send-manager-report', [
        config('helpdesk.manager_report_email'),
        'week',
    ])->weeklyOn(1, '08:30')->withoutOverlapping();
}
