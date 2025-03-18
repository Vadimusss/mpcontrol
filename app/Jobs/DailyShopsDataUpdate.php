<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\AddShopWbListGoods;
use App\Jobs\СheckApiKey;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use Throwable;

class DailyShopsDataUpdate implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->get();

        $shops->each(function ($shop) {
            СheckApiKey::dispatch($shop->apiKey);
            AddShopWbListGoods::dispatch($shop);
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('DailyShopsDataUpdate', $exception);
    }
}
