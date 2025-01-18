<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\AddTodayWbNmReportDetailHistory;
// use App\Jobs\TestJobWithError;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

class DailyDataUpdate implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->with('goods')->get();

        $shops->each(function ($shop, int $key) {
            $shopNmIds = $shop->goods()->pluck('nm_id')->toArray();
            $chunks = array_chunk($shopNmIds, 20);

            $jobs = array_map(function ($chunk) use ($shop) {
                return new AddTodayWbNmReportDetailHistory($shop, $chunk);
            }, $chunks);

            // array_unshift($jobs, new TestJobWithError);
            Bus::chain($jobs)->onQueue('api')->dispatch();
        });
    }
}
