<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:generate-weekly-cash')->weeklyOn(2, '05:00');
Schedule::command('app:generate-division-codes')->days([2, 4])->at('05:00');
Schedule::command('app:generate-daily-codes')->dailyAt('00:01');
Schedule::command('expire:schedules')->everyMinute();
Schedule::command('app:send-debt-reminder')->dailyAt('09:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
