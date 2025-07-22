<?php

namespace App\Jobs;

use Illuminate\Support\Arr;
use App\Models\Shop;
use App\Jobs\AddWbAdvV2Fullstats;
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

            // $shop->wbAdvV2FullstatsWbAdverts()->where('date', '=', $date)->delete();
            $advertIds = $shop->wbAdvV1PromotionCounts()
                ->where('shop_id', $shop->id)
                ->where(function ($query) use ($date) {
                    $query->where('status', 7)
                        ->where('change_time', '>=', $date)
                        ->orWhereIn('status', [9, 11]);
                })
                ->pluck('advert_id')
                ->toArray();

            $fullstatsPayload = array_map(function ($advertId) use ($date) {
                return [
                    'id' => $advertId,
                    'dates' => [$date],
                ];
            }, $advertIds);
            $fullstatsChunks = array_chunk($fullstatsPayload, 100);
            $fullstatsJobs = Arr::map($fullstatsChunks, function (array $chunk, int $index) use ($shop) {
                $delay = ($index == 0) ? 1 : 59;
                return (new AddWbAdvV2Fullstats($shop, $chunk))->delay($delay);
            });
            $fullstatsJobs = Arr::prepend($fullstatsJobs, new AddWbAdvV1PromotionCount($shop));

            $shop->WbNmReportDetailHistory()->where('dt', '=', $date)->delete();
            $shopGoods = $shop->goods();
            $shopNmIds = $shopGoods->pluck('nm_id')->toArray();
            $chunks = array_chunk($shopNmIds, 20);
            $nmReportDetailHistoryJobs = array_map(function ($chunk) use ($shop, $period) {
                return (new AddWbNmReportDetailHistory($shop, $chunk, $period))->delay(20);
            }, $chunks);

            Bus::batch([
                $fullstatsJobs,
                $nmReportDetailHistoryJobs,
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
