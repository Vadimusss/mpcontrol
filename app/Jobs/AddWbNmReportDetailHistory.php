<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\Good;
use App\Services\WbApiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Throwable;

class AddWbNmReportDetailHistory implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Shop $shop,
        public array $nmIds,
        public array $period
    )
    {
        $this->shop = $shop;
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
                    'imt_name' => $row['imtName'],
                    'vendor_code' => $row['vendorCode'],
                    'dt' => $day['dt'],
                    'open_card_count' => $day['openCardCount'],
                    'add_to_cart_count' => $day['addToCartCount'],
                    'orders_count' => $day['ordersCount'],
                    'orders_sum_rub' => $day['ordersSumRub'],
                    'buyouts_count' => $day['buyoutsCount'],
                    'buyouts_sum_rub' => $day['buyoutsSumRub'],
                    'buyout_percent' => $day['buyoutPercent'],
                    'add_to_cart_conversion' => $day['addToCartConversion'],
                    'cart_to_order_conversion' => $day['cartToOrderConversion'],
                ]);
            });
        });

        // dump($wbGoodListData);
    }

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