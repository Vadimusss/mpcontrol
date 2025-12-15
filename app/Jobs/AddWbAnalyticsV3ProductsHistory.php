<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\Good;
use App\Services\WbApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use Illuminate\Support\Collection;
use Throwable;

class AddWbAnalyticsV3ProductsHistory implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public Collection $goods,
        public string $date
    ) {
        $this->shop = $shop;
        $this->goods = $goods;
        $this->date = $date;
    }

    public $timeout = 180;
    public $backoff = 20;
    public $tries = 3;

    public function handle(): void
    {
        $goodsMap = $this->goods->pluck('id', 'nm_id');

        $api = new WbApiService($this->shop->apiKey->key);
        $nmIds = $goodsMap->keys()->toArray();
        $WbAnalyticsV3ProductsHistoryData = $api->getApiAnalyticsV3SalesFunnelProductsHistory($nmIds, $this->date);

        if ($WbAnalyticsV3ProductsHistoryData->isNotEmpty()) {

            $notEmptyData = $WbAnalyticsV3ProductsHistoryData->filter(function ($row) {
                return !empty($row['history']) && isset($row['history'][0]);
            });

            if ($notEmptyData->isNotEmpty()) {
                $receivedNmIds = $notEmptyData->pluck('product.nmId')->unique()->toArray();

                $receivedGoodIds = $goodsMap
                    ->only($receivedNmIds)
                    ->values()
                    ->toArray();

                DB::table('wb_analytics_v3_products_histories')
                    ->whereIn('good_id', $receivedGoodIds)
                    ->where('date', $this->date)
                    ->delete();

                $notEmptyData->each(function ($row) {
                    $product = $row['product'];

                    $good = Good::where('nm_id', $product['nmId'])
                        ->where('shop_id', $this->shop->id)
                        ->first();

                    $history = $row['history'][0];

                    $good->wbAnalyticsV3ProductsHistory()->create([
                        'nm_id' => $product['nmId'],
                        'title' => $product['title'],
                        'vendor_code' => $product['vendorCode'],
                        'brand_name' => $product['brandName'],
                        'subject_id' => $product['subjectId'],
                        'subject_name' => $product['subjectName'],
                        'date' => $history['date'],
                        'open_count' => $history['openCount'],
                        'cart_count' => $history['cartCount'],
                        'order_count' => $history['orderCount'],
                        'order_sum' => $history['orderSum'],
                        'buyout_count' => $history['buyoutCount'],
                        'buyout_sum' => $history['buyoutSum'],
                        'buyout_percent' => $history['buyoutPercent'],
                        'add_to_cart_conversion' => $history['addToCartConversion'],
                        'cart_to_order_conversion' => $history['cartToOrderConversion'],
                        'add_to_wishlist_count' => $history['addToWishlistCount'],
                    ]);
                });
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->dispatchNextJobInChain();
        JobFailed::dispatch('AddWbAnalyticsV3ProductsHistory', $exception);
        try {
            Log::error($exception->getMessage());
        } catch (Throwable $exception) {
            Log::error($exception->getMessage());
        }
    }
}
