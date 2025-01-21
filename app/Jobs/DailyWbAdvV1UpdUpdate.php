<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\AddWbAdvV1Upd;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class DailyWbAdvV1UpdUpdate implements ShouldQueue
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

        $shops->each(function ($shop) {
            $shopGoods = $shop->goods();

            DB::table('wb_adv_v1_upds')->delete();

            $period = [
                'from' => date('Y-m-d', strtotime("-31 days")),
                'to' => date('Y-m-d', time()),
            ];

            AddWbAdvV1Upd::dispatch($shop->apiKey->key, $period);
        });
    }
}
