<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\WbAdvV1PromotionAdverts;
use App\Models\WbAdvV1PromotionNm;
use App\Models\WbAdvV1PromotionNmCpm;
use App\Services\WbApiService;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Throwable;

class UpdateWbAdvV1PromotionAdverts implements ShouldQueue
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
            ->where('type', 8)
            ->where(function ($query) use ($date) {
                $query->where('status', 7)
                    ->where('change_time', '>=', $date)
                    ->orWhereIn('status', [9, 11]);
            })
            ->pluck('advert_id')
            ->toArray();

        if (empty($advertIds)) {
            $message = "Нет активных рекламных кампаний для магазина {$this->shop->name}";
            JobSucceeded::dispatch('UpdateAdvV1PromotionAdverts', 0, $message);
            return;
        }

        $chunkedAdvertIds = array_chunk($advertIds, 50);
        $addedAdvertsCount = 0;
        $startTime = microtime(true);

        foreach ($chunkedAdvertIds as $chunk) {
            $response = $apiService->getWbAdvV1PromotionAdverts($chunk);
            $this->processAdvertsBatch($response);
            $addedAdvertsCount += count($response);

            usleep(200000);
        }

        $message = "{$addedAdvertsCount} рекламных кампаний единая ставка магазина {$this->shop->name} обработаны!";
        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('UpdateAdvV1PromotionAdverts', $duration, $message);
    }

    protected function processAdvertsBatch($adverts): void
    {
        $advertIds = $adverts->pluck('advertId')->toArray();

        WbAdvV1PromotionAdverts::where('shop_id', $this->shop->id)
            ->whereIn('advert_id', $advertIds)
            ->delete();

        $insertData = [];
        $nmsData = [];
        $nmCpmData = [];

        foreach ($adverts as $advertData) {
            $advertId = $advertData['advertId'];
            $autoParams = $advertData['autoParams'];

            $insertData[] = [
                'shop_id' => $this->shop->id,
                'advert_id' => $advertId,
                'name' => $advertData['name'],
                'type' => $advertData['type'],
                'status' => $advertData['status'],
                'payment_type' => $advertData['paymentType'],
                'bid_type' => $advertData['bid_type'],
                'daily_budget' => $advertData['dailyBudget'],
                'start_time' => Carbon::parse($advertData['startTime']),
                'end_time' => Carbon::parse($advertData['endTime']),
                'create_time' => Carbon::parse($advertData['createTime']),
                'change_time' => Carbon::parse($advertData['changeTime']),
                'cpm' => $autoParams['cpm'],
                'subject_id' => $autoParams['subject']['id'],
                'subject_name' => $autoParams['subject']['name'],
                'active_carousel' => $autoParams['active']['carousel'],
                'active_recom' => $autoParams['active']['recom'],
                'active_booster' => $autoParams['active']['booster'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (!empty($autoParams['nms'])) {
                foreach ($autoParams['nms'] as $nm) {
                    $nmsData[$advertId][] = $nm;
                }
            }

            if (!empty($autoParams['nmCPM'])) {
                foreach ($autoParams['nmCPM'] as $nmCpm) {
                    $nmCpmData[$advertId][] = [
                        'nm' => $nmCpm['nm'] ?? null,
                        'cpm' => $nmCpm['cpm'] ?? null,
                    ];
                }
            }
        }

        if (!empty($insertData)) {
            WbAdvV1PromotionAdverts::insert($insertData);

            $insertedAdverts = WbAdvV1PromotionAdverts::where('shop_id', $this->shop->id)
                ->whereIn('advert_id', $advertIds)
                ->get()
                ->keyBy('advert_id');

            $this->insertRelatedData($insertedAdverts, $nmsData, $nmCpmData);
        }
    }

    protected function insertRelatedData($insertedAdverts, $nmsData, $nmCpmData): void
    {
        $nmsInsertData = [];
        $nmCpmInsertData = [];

        foreach ($insertedAdverts as $advertId => $advert) {
            if (isset($nmsData[$advertId])) {
                foreach ($nmsData[$advertId] as $nm) {
                    $nmsInsertData[] = [
                        'wb_adv_v1_promotion_adverts_id' => $advert->id,
                        'nm' => $nm,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (isset($nmCpmData[$advertId])) {
                foreach ($nmCpmData[$advertId] as $nmCpm) {
                    $nmCpmInsertData[] = [
                        'wb_adv_v1_promotion_adverts_id' => $advert->id,
                        'nm' => $nmCpm['nm'],
                        'cpm' => $nmCpm['cpm'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($nmsInsertData)) {
            WbAdvV1PromotionNm::insert($nmsInsertData);
        }

        if (!empty($nmCpmInsertData)) {
            WbAdvV1PromotionNmCpm::insert($nmCpmInsertData);
        }
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateAdvV1PromotionAdverts', $exception);
    }
}
