<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Processes a small batch every 30 minutes rather than one long unattended
// run, so a killed container/sleeping laptop only loses at most 30 minutes
// of progress instead of hours. financials:sync already skips anything
// synced within the last 24h, so overlapping/duplicate runs are harmless.
Schedule::command('financials:sync --limit=50')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/financials-sync.log'));

// Refreshes the stock/theme master list once a day.
Schedule::command('stocks:sync')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/stocks-sync.log'));
