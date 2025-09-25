<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\WbAdvV0AuctionAdvert;
use App\Services\WbApiService;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Throwable;

class UpdateWbAdvV0AuctionAdvert implements ShouldQueue
{
    use Queueable;

    protected $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function handle(): void
    {
        $apiService = new WbApiService($this->shop->apiKey->key);
        $date = Carbon::now()->subDays(31);

        $advertIds = $this->shop->wbAdvV1PromotionCounts()
            ->where('shop_id', $this->shop->id)
            ->where('type', 9)
            ->where(function ($query) use ($date) {
                $query->where('status', 7)
                    ->where('change_time', '>=', $date)
                    ->orWhereIn('status', [9, 11]);
            })
            ->pluck('advert_id')
            ->toArray();

        if (empty($advertIds)) {
            $message = "Нет активных рекламных кампаний для магазина {$this->shop->name}";
            JobSucceeded::dispatch('UpdateWbAdvV0AuctionAdvert', 0, $message);
            return;
        }

        $chunkedAdvertIds = array_chunk($advertIds, 50);
        $addedAdvertsCount = 0;
        $startTime = microtime(true);

        foreach ($chunkedAdvertIds as $chunk) {
            $response = $apiService->getWbAdvV0AuctionAdvert($chunk);
            if (!empty($response)) {
                $this->processAdvertsBatch($response);
            }
            $addedAdvertsCount += count($response);

            usleep(200000);
        }

        $message = "{$addedAdvertsCount} рекламных кампаний type: аукцион магазина {$this->shop->name} обработаны!";
        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('UpdateWbAdvV0AuctionAdvert', $duration, $message);
    }

    protected function processAdvertsBatch($adverts): void
    {
        $advertIds = $adverts->pluck('id')->toArray();

        WbAdvV0AuctionAdvert::where('shop_id', $this->shop->id)
            ->whereIn('advert_id', $advertIds)
            ->delete();

        $insertData = [];

        foreach ($adverts as $advertData) {
            $advertId = $advertData['id'];
            $settings = $advertData['settings'];
            $timestamps = $advertData['timestamps'];
            $nmsSetting = $advertData['nm_settings'][0];

            $insertData[] = [
                'shop_id' => $this->shop->id,
                'advert_id' => $advertId,
                'bid_type' => $advertData['bid_type'],
                'bids_recommendations' => $nmsSetting['bids']['recommendations'],
                'bids_search' => $nmsSetting['bids']['search'],
                'nm_id' => $nmsSetting['nm_id'],
                'subject_id' => $nmsSetting['subject']['id'],
                'subject_name' => $nmsSetting['subject']['name'],
                'name' => $settings['name'],
                'payment_type' => $settings['payment_type'],
                'placements_recommendations' => $settings['placements']['recommendations'],
                'placements_search' => $settings['placements']['search'],
                'status' => $advertData['status'],
                'created' => $timestamps['created'],
                'deleted' => $timestamps['deleted'],
                'started' => $timestamps['started'],
                'updated' => $timestamps['updated'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($insertData)) {
            WbAdvV0AuctionAdvert::insert($insertData);
        }
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateWbAdvV0AuctionAdvert', $exception);
    }
}
