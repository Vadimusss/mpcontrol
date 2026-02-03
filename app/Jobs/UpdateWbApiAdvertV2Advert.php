<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\WbApiAdvertV2Advert;
use App\Models\WbApiAdvertV2AdvertNm;
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
        $nmsData = [];

        foreach ($adverts as $advertData) {
            $advertId = $advertData['id'];
            $settings = $advertData['settings'];
            $timestamps = $advertData['timestamps'];
            $nmSettings = $advertData['nm_settings'];

            $insertData[] = [
                'shop_id' => $shop->id,
                'advert_id' => $advertId,
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

            if (!empty($nmSettings)) {
                foreach ($nmSettings as $nm) {
                    $nmsData[$advertId][] = $nm;
                }
            }
        }

        if (!empty($insertData)) {
            WbApiAdvertV2Advert::insert($insertData);

            $insertedAdverts = WbApiAdvertV2Advert::where('shop_id', $shop->id)
                ->whereIn('advert_id', $advertIds)
                ->get()
                ->keyBy('advert_id');

            $this->insertRelatedData($insertedAdverts, $nmsData);
        }
    }

    protected function insertRelatedData($insertedAdverts, $nmsData): void
    {
        $nmsInsertData = [];

        foreach ($insertedAdverts as $advertId => $advert) {
            if (isset($nmsData[$advertId])) {
                foreach ($nmsData[$advertId] as $nm) {
                    $bidsKopecks = $nm['bids_kopecks'];
                    $subject = $nm['subject'];

                    $nmsInsertData[] = [
                        'wb_api_advert_v2_advert_id' => $advert->id,
                        'bids_kopecks_search' => $bidsKopecks['search'],
                        'bids_kopecks_recommendations' => $bidsKopecks['recommendations'],
                        'nm_id' => $nm['nm_id'],
                        'subject_id' => $subject['id'],
                        'subject_name' => $subject['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($nmsInsertData)) {
            WbApiAdvertV2AdvertNm::insert($nmsInsertData);
        }
    }

    public function failed(Throwable $exception): void
    {
        JobFailed::dispatch('WbApiAdvertV2Advert', $exception);
    }
}
