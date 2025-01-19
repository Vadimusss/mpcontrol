<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\Good;
use App\Services\WbApiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class AddWbNmReportDetailHistory implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Shop $shop, public array $nmIds, public array $period)
    {
        $this->shop = $shop->withoutRelations();
        $this->nmIds = $nmIds;
        $this->period = $period;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $api = new WbApiService($this->shop->apiKey->key);
        $WbNmReportDetailHistoryData = $api->getApiV2NmReportDetailHistory($this->nmIds, $this->period);

        $WbNmReportDetailHistoryData->each(function ($row) {
            $good = Good::firstWhere('nm_id', $row['nmID']);

            array_walk($row['history'], function ($day) use ($good, $row) {
                $good->WbNmReportDetailHistory()->create([
                    'nm_id' => $row['nmID'],
                    'imtName' => $row['imtName'],
                    'vendor_code' => $row['vendorCode'],
                    'dt' => $day['dt'],
                    'openCardCount' => $day['openCardCount'],
                    'addToCartCount' => $day['addToCartCount'],
                    'ordersCount' => $day['addToCartConversion'],
                    'ordersSumRub' => $day['ordersCount'],
                    'buyoutsCount' => $day['ordersSumRub'],
                    'buyoutsSumRub' => $day['cartToOrderConversion'],
                    'buyoutPercent' => $day['buyoutsCount'],
                    'addToCartConversion' => $day['buyoutsSumRub'],
                    'cartToOrderConversion' => $day['buyoutPercent'],
                ]);
            });
        });

        // dump($wbGoodListData);
    }

        /**
         * Получить посредника, через которого должно пройти задание.
         *
         * @return array<int, object>
        */

/*     public function middleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->releaseAfter(60)];
    } */

    public function failed(?Throwable $exception): void
    {
        $this->dispatchNextJobInChain();
        try {
            Log::error($exception->getMessage());
        } catch (Throwable $exception) {
            Log::error($exception->getMessage());
        }
    }
}