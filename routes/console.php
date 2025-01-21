<?php

use App\Jobs\DailyWbNmReportDetailHistoryUpdate;
use App\Jobs\DailyWbAdvV1UpdUpdate;
use App\Jobs\ReloadYesterdayWbNmReportDetailHistory;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new DailyWbNmReportDetailHistoryUpdate)->everyMinute();

// Schedule::job(new ReloadYesterdayWbNmReportDetailHistory, 'main')->everyMinute();

// Schedule::job(new DailyWbAdvV1UpdUpdate, 'api')->everyMinute();