<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\AddWbNmReportDetailHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class DailyWbNmReportDetailHistoryUpdate implements ShouldQueue
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
            $shopGoods = $shop->goods();
            $today = date('Y-m-d', time());
            DB::table('wb_nm_report_detail_histories')->where('dt', '=', $today)->delete();

            $period = [
                'begin' => $today,
                'end' => $today,
            ];
            $shopNmIds = $shopGoods->pluck('nm_id')->toArray();
            $chunks = array_chunk($shopNmIds, 20);
            $jobs = array_map(function ($chunk) use ($shop, $period) {
                return new AddWbNmReportDetailHistory($shop, $chunk, $period);
            }, $chunks);

            Bus::chain($jobs)->onQueue('api')->dispatch();
        });
    }
}
