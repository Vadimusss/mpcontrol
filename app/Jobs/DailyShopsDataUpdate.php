<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\AddShopWbListGoods;
use App\Jobs\СheckApiKey;
use App\Jobs\UpdateNsiFromGoogleSheets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Illuminate\Support\Facades\Bus;
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

        $shops->each(function ($shop) {
            СheckApiKey::dispatch($shop->apiKey);
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
