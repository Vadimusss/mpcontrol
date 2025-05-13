<?php

namespace App\Listeners;

use App\Events\ShopCreated;
use App\Jobs\AddShopWbListGoods;
use App\Jobs\СheckApiKey;

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
