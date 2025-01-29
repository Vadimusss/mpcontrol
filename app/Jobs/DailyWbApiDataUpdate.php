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
            $period = [
                'begin' => $day,
                'end' => $day,
            ];

            $shop->WbNmReportDetailHistory()->where('dt', '=', $day)->delete();

            $shopNmIds = $shopGoods->pluck('nm_id')->toArray();
            $chunks = array_chunk($shopNmIds, 20);
            $jobs = array_map(function ($chunk) use ($shop, $period) {
                return new AddWbNmReportDetailHistory($shop, $chunk, $period);
            }, $chunks);

            Bus::batch([
                $jobs,
                [new AddWbAdvV1Upd($shop, $period)],
                [new AddWbV1SupplierOrders($shop, $day)],
            ])->then(function (Batch $batch) use ($shop, $day) {
                GenerateSalesFunnelReport::dispatch($shop, $day);
            })->dispatch();
        });
    }
}
