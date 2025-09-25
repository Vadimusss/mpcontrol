<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\UpdateWbAdvV0AuctionAdvert;
use App\Jobs\UpdateAdvV1PromotionAdverts;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        $shops = Shop::without(['owner', 'customers'])->get();

        $shops->each(function ($shop) {
            UpdateWbAdvV0AuctionAdvert::dispatch($shop);
            UpdateWbAdvV1PromotionAdverts::dispatch($shop);
/*             $date = date('Y-m-d', strtotime("-{$this->daysAgo} days"));

            $advertIds = $shop->wbAdvV1PromotionCounts()
                ->where('shop_id', $shop->id)
                ->where(function ($query) use ($date) {
                    $query->where('status', 7)
                        ->where('change_time', '>=', $date)
                        ->orWhereIn('status', [9, 11]);
                })
                ->pluck('advert_id')
                ->toArray();

            $fullstatsChunks = array_chunk($advertIds, 100);
            $fullstatsJobs = array_map(function ($chunk) use ($shop, $date) {
                return (new AddWbAdvV3Fullstats($shop, $chunk, $date))->delay(60);
            }, $fullstatsChunks);

            Bus::batch([$fullstatsJobs])->allowFailures()->dispatch(); */
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('TestJob', $exception);
    }
}
