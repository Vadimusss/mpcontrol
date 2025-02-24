<?php

use App\Jobs\DailyWbApiDataUpdate;
use App\Jobs\DailyShopsDataUpdate;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new DailyShopsDataUpdate)->dailyAt('1:00');

Schedule::job(new DailyWbApiDataUpdate(1))->dailyAt('2:00');

// Schedule::job(new DailyWbAdvV1UpdUpdate, 'api')->everyMinute();