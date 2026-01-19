<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:generate-weekly-cash')->weeklyOn(2, '05:00');
Schedule::command('app:generate-division-codes')->days([2, 4])->at('05:00');
Schedule::command('expire:schedules')->everyMinute();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
