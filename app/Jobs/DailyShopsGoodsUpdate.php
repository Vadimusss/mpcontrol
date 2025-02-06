<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\AddShopWbListGoods;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;

class DailyShopsGoodsUpdate implements ShouldQueue
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
        $shops = Shop::without(['owner', 'customers'])->get();

        $shops->each(function ($shop) {
            AddShopWbListGoods::dispatch($shop);           
        });
    }
}
