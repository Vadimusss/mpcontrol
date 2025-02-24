<?php

namespace App\Listeners;

use App\Events\ShopCreated;
use App\Jobs\AddShopWbListGoods;
use App\Jobs\СheckApiKey;
use App\Models\Good;
use App\Models\WbListGood;
use App\Models\WbListGoodSize;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateShopWbListGoodsData
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ShopCreated $event): void
    {
        $shop = $event->shop;

        AddShopWbListGoods::dispatch($shop);
        СheckApiKey::dispatch($shop->apiKey);
    }
}
