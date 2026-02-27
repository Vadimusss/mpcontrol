<?php

namespace App\Jobs;

use Illuminate\Support\Arr;
use App\Models\Shop;
use App\Jobs\AddWbAdvV2Fullstats;
use App\Jobs\AddWbAdvV1PromotionCount;
use App\Jobs\AddWbAnalyticsV3ProductsHistory;
// use App\Jobs\AddWbAdvV1Upd;
use App\Jobs\AddWbV1SupplierOrders;
use App\Jobs\AddWbV1SupplierStocks;
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
        $shops = Shop::without(['owner', 'customers'])->with('goods')->whereHas('apiKey', function ($query) {
            $query->where('is_active', true);
        })->get();

        $shops->each(function ($shop, int $key) {
            $daysAgo = $this->daysAgo;
            $date = date('Y-m-d', strtotime("-{$daysAgo} days"));

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

            $batchChains = [
                $fullstatsJobs,
                $productsHistoryJobs,
                [new AddWbV1SupplierOrders($shop, $date)],
            ];

            if ($daysAgo == 0) {
                $batchChains[] = [new AddWbV1SupplierStocks($shop, $date)];
            }

            Bus::batch($batchChains)
                ->then(function (Batch $batch) use ($shop, $date, $daysAgo) {
                    GenerateSalesFunnelReport::dispatch($shop, $date);
                    Bus::chain(array_filter([
                        $daysAgo == 0 ? new GenerateStocksAndOrdersReport($shop, $date) : null,
                        new UpdateStocksAndOrdersReport($shop, $date),
                    ]))->dispatch();
                })->allowFailures()->dispatch();
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('DailyWbApiDataUpdate', $exception);
    }
}
