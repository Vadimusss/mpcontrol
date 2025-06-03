<?php

namespace App\Listeners;

use App\Events\ShopCreated;
use App\Jobs\AddShopWbListGoods;
use App\Jobs\СheckApiKey;
use App\Jobs\UpdateNsiFromGoogleSheets;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Illuminate\Support\Facades\Bus;
use Throwable;

class UpdateShopWbListGoodsData
{
    public function __construct() {}

    public function handle(ShopCreated $event): void
    {
        $startTime = microtime(true);
        $shop = $event->shop;

        СheckApiKey::dispatch($shop->apiKey);

        Bus::chain([
            new AddShopWbListGoods($shop),
            new UpdateNsiFromGoogleSheets($shop->id),
        ])->dispatch();

        $duration = microtime(true) - $startTime;

        JobSucceeded::dispatch('NewShopsDataUpdate', $duration);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('NewShopsDataUpdate', $exception);
    }
}
