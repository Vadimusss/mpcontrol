<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\AddWbNmReportDetailHistory;
use App\Jobs\AddWbAdvV1Upd;
use App\Jobs\AddWbV1SupplierOrders;
use App\Jobs\GenerateSalesFunnelReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class DailyWbApiDataUpdate implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $daysAgo = 0)
    {
        $this->daysAgo = $daysAgo;
    }

    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->with('goods')->get();

        $shops->each(function ($shop, int $key) {
            $shopGoods = $shop->goods();
            $day = date('Y-m-d',strtotime("-{$this->daysAgo} days"));
            DB::table('wb_nm_report_detail_histories')->where('dt', '=', $day)->delete();
            DB::table('wb_adv_v1_upds')->whereRaw("DATE(upd_time) = '{$day}'")->delete();
            DB::table('wb_v1_supplier_orders')->whereRaw("DATE(date) = '{$day}'")->delete();

            $period = [
                'begin' => $day,
                'end' => $day,
            ];
            $shopNmIds = $shopGoods->pluck('nm_id')->toArray();
            $chunks = array_chunk($shopNmIds, 20);
            $apiKey = $shop->apiKey->key;

            $jobs = array_map(function ($chunk) use ($apiKey, $period) {
                return new AddWbNmReportDetailHistory($apiKey, $chunk, $period);
            }, $chunks);

            Bus::batch([
                $jobs,
                [new AddWbAdvV1Upd($apiKey, $period)],
                [new AddWbV1SupplierOrders($apiKey, $day)],
            ])->then(function (Batch $batch) use ($day) {
                GenerateSalesFunnelReport::dispatch($day);
            })->dispatch();
        });
    }
}
