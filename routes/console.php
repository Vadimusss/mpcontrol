<?php

use App\Jobs\DailyWbApiDataUpdate;
use App\Jobs\DailyShopsGoodsUpdate;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new DailyShopsGoodsUpdate)->dailyAt('22:00');

Schedule::job(new DailyWbApiDataUpdate)->dailyAt('23:00');

// Schedule::job(new DailyWbAdvV1UpdUpdate, 'api')->everyMinute();