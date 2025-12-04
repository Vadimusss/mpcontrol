<?php

namespace App\Jobs;

use Illuminate\Support\Arr;
use App\Models\Shop;
use App\Jobs\AddWbAnalyticsV3ProductsHistory;
use App\Jobs\AddWbAdvV1PromotionCount;
use App\Jobs\AddWbNmReportDetailHistory;
use App\Jobs\AddWbAdvV1Upd;
use App\Jobs\AddWbV1SupplierOrders;
use App\Jobs\UpdateWbV1SupplierStocks;
use App\Jobs\GenerateSalesFunnelReport;
use App\Jobs\GenerateStocksAndOrdersReport;
use App\Jobs\UpdateStocksAndOrdersReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Batch;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use App\Events\JobFailed;
use Throwable;

class TestJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $daysAgo = 0)
    {
        $this->daysAgo = $daysAgo;
    }

    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->with('goods')->get();

        $shops->each(function ($shop, int $key) {
            $date = date('Y-m-d', strtotime("-{$this->daysAgo} days"));

            $shopGoods = $shop->goods();
            $shopNmIds = $shopGoods->pluck('nm_id')->toArray();

            $chunks = array_chunk($shopNmIds, 20);
            $nmReportDetailHistoryJobs = array_map(function ($chunk) use ($shop, $date) {
                return (new AddWbAnalyticsV3ProductsHistory($shop, $chunk, $date))->delay(20);
            }, $chunks);

            Bus::batch([
                $nmReportDetailHistoryJobs,
            ])->then(function () {

            })->allowFailures()->dispatch();
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('DailyWbApiDataUpdate', $exception);
    }
}
