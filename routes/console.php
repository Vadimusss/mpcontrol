<?php

use App\Jobs\DailyWbApiDataUpdate;
use App\Jobs\DailyShopsDataUpdate;
use App\Jobs\MigrateSalesFunnelData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new DailyShopsDataUpdate)->dailyAt('00:30');

Schedule::job(new DailyWbApiDataUpdate(1))->dailyAt('01:00');

Schedule::job(new DailyWbApiDataUpdate())->dailyAt('07:00');

Schedule::job(new DailyWbApiDataUpdate())->dailyAt('09:00');

Schedule::job(new DailyWbApiDataUpdate())->dailyAt('11:00');

Schedule::job(new DailyWbApiDataUpdate())->dailyAt('13:00');

Schedule::job(new DailyWbApiDataUpdate())->dailyAt('15:00');

Schedule::job(new DailyWbApiDataUpdate())->dailyAt('17:00');

Schedule::job(new DailyWbApiDataUpdate())->dailyAt('19:00');

Schedule::job(new DailyWbApiDataUpdate())->dailyAt('21:00');

// Schedule::job(new MigrateSalesFunnelData, 'api')->everyMinute();