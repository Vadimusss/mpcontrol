<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\GenerateMainViewCache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\JobFailed;
use Throwable;

class UpdateAllShopMainViewCache implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $daysAgo = 0)
    {
        $this->daysAgo = $daysAgo;
    }

    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->with('goods')->get();

        $shops->each(function ($shop) {
            GenerateMainViewCache::dispatch($shop);
        });
    }

    public function failed(Throwable $exception): void
    {
        JobFailed::dispatch('DailyWbApiDataUpdate', $exception);
    }
}
