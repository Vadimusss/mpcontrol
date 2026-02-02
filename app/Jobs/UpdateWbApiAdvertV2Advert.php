<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\WbApiAdvertV2Advert;
use App\Services\WbApiService;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Throwable;

class UpdateWbApiAdvertV2Advert implements ShouldQueue
{
    use Queueable;

    public function __construct(private int $shopId) {}

    public function handle(): void
    {
        $shop = Shop::find($this->shopId);
        $apiService = new WbApiService($shop->apiKey->key);
        $date = Carbon::now()->subDays(31);

        $advertIds = $shop->wbAdvV1PromotionCounts()
            ->where('shop_id', $shop->id)
            ->where(function ($query) use ($date) {
                $query->where('status', 7)
                    ->where('change_time', '>=', $date)
                    ->orWhereIn('status', [9, 11]);
            })
            ->pluck('advert_id')
            ->toArray();

        if (empty($advertIds)) {
            $message = "Нет активных рекламных кампаний для магазина {$shop->name}";
            JobSucceeded::dispatch('WbApiAdvertV2Advert', 0, $message);
            return;
        }

        $chunkedAdvertIds = array_chunk($advertIds, 50);
        $addedAdvertsCount = 0;
        $startTime = microtime(true);

        foreach ($chunkedAdvertIds as $chunk) {
            $response = $apiService->getWbApiAdvertV2Advert($chunk);
            if (!empty($response)) {
                $this->processAdvertsBatch($shop, $response);
            }
            $addedAdvertsCount += count($response);

            usleep(200000);
        }

        $message = "{$addedAdvertsCount} рекламных кампаний магазина {$shop->name} обработаны!";
        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('WbApiAdvertV2Advert', $duration, $message);
    }

    protected function processAdvertsBatch($shop, $adverts): void
    {
        $advertIds = $adverts->pluck('id')->toArray();

        WbApiAdvertV2Advert::where('shop_id', $shop->id)
            ->whereIn('advert_id', $advertIds)
            ->delete();

        $insertData = [];

        foreach ($adverts as $advertData) {
            $settings = $advertData['settings'];
            $timestamps = $advertData['timestamps'];

            $insertData[] = [
                'shop_id' => $shop->id,
                'advert_id' => $advertData['id'],
                'bid_type' => $advertData['bid_type'],
                'status' => $advertData['status'],
                'settings_name' => $settings['name'],
                'settings_payment_type' => $settings['payment_type'],
                'placements_search' => $settings['placements']['search'],
                'placements_recommendations' => $settings['placements']['recommendations'],
                'timestamps_created' => $timestamps['created'],
                'timestamps_deleted' => $timestamps['deleted'],
                'timestamps_started' => $timestamps['started'],
                'timestamps_updated' => $timestamps['updated'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($insertData)) {
            WbApiAdvertV2Advert::insert($insertData);
        }
    }

    public function failed(Throwable $exception): void
    {
        JobFailed::dispatch('WbApiAdvertV2Advert', $exception);
    }
}
