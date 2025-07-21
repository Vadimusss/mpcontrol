<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Jobs\AddWbAdvV2Fullstats;
use App\Jobs\UpdateNsiFromGoogleSheets;
use App\Jobs\UpdateWbAdvV2FullstatsForDate;
use App\Jobs\ClearAddWbAdvV2FullstatsForDate;
use App\Jobs\GenerateSalesFunnelReport;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\ClosureJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Events\JobFailed;
use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class UpdateSalesFunnelReport implements ShouldQueue
{
    use Queueable, Batchable;

    public function __construct() {}

    public function handle()
    {
        $dates = collect(range(1, 13))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        });

        $shops = Shop::without(['owner', 'customers'])->get();

        $shops->each(function ($shop) use ($dates) {
            $shopFullUpdateJobs = [];

            $dates->each(function ($date) use ($shop, &$shopFullUpdateJobs) {
                $advertIds = $shop->wbAdvV1PromotionCounts()
                    ->where('shop_id', $shop->id)
                    ->where(function ($query) use ($date) {
                        $query->where('status', 7)
                            ->where('change_time', '>=', $date)
                            ->orWhereIn('status', [9, 11]);
                    })
                    ->pluck('advert_id')
                    ->toArray();

                $fullstatsApiPayload = array_map(function ($advertId) use ($date) {
                    return [
                        'id' => $advertId,
                        'dates' => [$date],
                    ];
                }, $advertIds);

                $fullstatsApiChunks = array_chunk($fullstatsApiPayload, 100);

                $fullstatsJobs = Arr::map($fullstatsApiChunks, function (array $chunk, int $index) use ($shop) {
                    $delay = ($index == 0) ? 1 : 60;
                    return (new AddWbAdvV2Fullstats($shop, $chunk))->delay($delay);
                });

                $shopFullUpdateJobs[] = Bus::batch([array_merge(
                    [new ClearAddWbAdvV2FullstatsForDate($shop, $date)],
                    $fullstatsJobs,
                    [new GenerateSalesFunnelReport($shop, $date)]
                )])->then(function (Batch $batch) {})->allowFailures();
            });

            Bus::chain($shopFullUpdateJobs)->dispatch();
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateSalesFunnelReport', $exception);
    }
}
