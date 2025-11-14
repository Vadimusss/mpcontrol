<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Jobs\AddWbAdvV2Fullstats;
use App\Jobs\UpdateNsiFromGoogleSheets;
use App\Jobs\UpdateWbAdvV2FullstatsForDate;
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
        $dates = collect(range(2, 32))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        });

        $fullUpdateDates = $dates->take(13);
        $generateOnlyDates = $dates->slice(13);

        $shops = Shop::without(['owner', 'customers'])->get();

        $shops->each(function ($shop) use ($fullUpdateDates, $generateOnlyDates) {
            $shopFullUpdateJobs = [];

            $fullUpdateDates->each(function ($date) use ($shop, &$shopFullUpdateJobs) {
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

                $shopFullUpdateJobs[] = Bus::batch([array_merge(
                    $fullstatsJobs,
                    [new GenerateSalesFunnelReport($shop, $date)]
                )])->then(function (Batch $batch) {})->allowFailures();
            });

            $generateJobs = $generateOnlyDates->map(function ($date) use ($shop) {
                return new GenerateSalesFunnelReport($shop, $date);
            })->toArray();

            $shopFullUpdateJobs = array_merge($shopFullUpdateJobs, $generateJobs);

            Bus::chain($shopFullUpdateJobs)->dispatch();
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateSalesFunnelReport', $exception);
    }
}
