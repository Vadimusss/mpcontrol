<?php

namespace App\Jobs;

use Illuminate\Support\Arr;
use App\Models\Shop;
use App\Jobs\AddWbAdvV2Fullstats;
use App\Jobs\AddWbAdvV1PromotionCount;
use App\Jobs\AddWbAnalyticsV3ProductsHistory;
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

class DailyWbApiDataUpdate implements ShouldQueue
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
            $period = [
                'begin' => $date,
                'end' => $date,
            ];

            $advertIds = $shop->wbAdvV1PromotionCounts()
                ->where('shop_id', $shop->id)
                ->where(function ($query) use ($date) {
                    $query->where('status', 7)
                        ->where('change_time', '>=', $date)
                        ->orWhereIn('status', [9, 11]);
                })
                ->pluck('advert_id')
                ->toArray();

            $fullstatsChunks = array_chunk($advertIds, 40);
            $fullstatsJobs = array_map(function ($chunk) use ($shop, $date) {
                return (new AddWbAdvV3Fullstats($shop, $chunk, $date))->delay(22);
            }, $fullstatsChunks);

            $fullstatsJobs = Arr::prepend($fullstatsJobs, new AddWbAdvV1PromotionCount($shop));

            $shopGoods = $shop->goods()->select('id', 'nm_id')->get();
            $chunks = $shopGoods->chunk(20);

            $productsHistoryJobs = [];

            foreach ($chunks as $chunk) {
                $productsHistoryJobs[] = (new AddWbAnalyticsV3ProductsHistory(
                    $shop,
                    $chunk,
                    $date
                ))->delay(20);
            }

            Bus::batch([
                $fullstatsJobs,
                $productsHistoryJobs,
                [new AddWbAdvV1Upd($shop, $period)],
                [new AddWbV1SupplierOrders($shop, $date)],
                [new UpdateWbV1SupplierStocks($shop)],
            ])->then(function (Batch $batch) use ($shop, $date) {
                GenerateSalesFunnelReport::dispatch($shop, $date);
                Bus::chain([
                    new GenerateStocksAndOrdersReport($shop, $date),
                    new UpdateStocksAndOrdersReport($shop, $date),
                ])->dispatch();
            })->allowFailures()->dispatch();
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('DailyWbApiDataUpdate', $exception);
    }
}
