<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tickets:create-recurring')->dailyAt('00:05')->withoutOverlapping();
Schedule::command('tickets:send-summaries daily')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('tickets:send-summaries weekly')->weeklyOn(1, '08:00')->withoutOverlapping();
