<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\AddShopWbListGoods;
use App\Jobs\СheckApiKey;
use App\Jobs\UpdateNsiFromGoogleSheets;
use App\Jobs\ProcessNmReportDownload;
use App\Jobs\UpdateWbNmReportFromTempData;
use App\Jobs\AddWbApiV3Warehouses;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Illuminate\Support\Facades\Bus;
use Carbon\Carbon;
use Throwable;

class DailyShopsDataUpdate implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $shops = Shop::without(['owner', 'customers'])->get();

        $dates = collect(range(2, 32))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        });

        $period = [
            'begin' => $dates->min(),
            'end' => $dates->max(),
        ];

        $shops->each(function ($shop) use ($period, $dates) {
            $UpdateNmReportDownloadChain[] = new ProcessNmReportDownload($shop, $period);

            foreach ($dates as $date) {
                $UpdateNmReportDownloadChain[] = new UpdateWbNmReportFromTempData($shop, $date);
            }

            Bus::chain($UpdateNmReportDownloadChain)->dispatch();

            СheckApiKey::dispatch($shop->apiKey);

            AddWbApiV3Warehouses::dispatch($shop);

            Bus::chain([
                new AddShopWbListGoods($shop),
                new UpdateNsiFromGoogleSheets($shop->id),
            ])->dispatch();
        });



        $duration = microtime(true) - $startTime;

        JobSucceeded::dispatch('DailyShopsDataUpdate', $duration);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('DailyShopsDataUpdate', $exception);
    }
}
