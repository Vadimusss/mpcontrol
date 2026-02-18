<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Good;
use App\Models\Shop;
use App\Models\Note;
use Carbon\Carbon;

class GoodDetailsCacheService
{
    public function getForGood(Good $good, Shop $shop, array $dates): ?array
    {
        $cacheKey = "good_details_cache:shop_{$shop->id}";
        
        $cachedData = Cache::get($cacheKey);
        
        if (!$cachedData) {
            return null;
        }

        $goodData = $cachedData['goods'][$good->id] ?? null;
        
        if (!$goodData) {
            return null;
        }

        $cachedDates = array_keys($goodData['salesData'] ?? []);
        $missingDates = array_diff($dates, $cachedDates);
        
        if (!empty($missingDates)) {
            return null;
        }

        return $this->processCachedData($goodData, $dates, $good, $shop);
    }

    public function clearForShop(Shop $shop): void
    {
        $cacheKey = "good_details_cache:shop_{$shop->id}";
        Cache::forget($cacheKey);
    }

    public function hasCacheForShop(Shop $shop): bool
    {
        $cacheKey = "good_details_cache:shop_{$shop->id}";
        return Cache::has($cacheKey);
    }

    public function getCacheInfo(Shop $shop): array
    {
        $cacheKey = "good_details_cache:shop_{$shop->id}";
        $cachedData = Cache::get($cacheKey);
        
        if (!$cachedData) {
            return [
                'exists' => false,
                'calculated_at' => null,
                'goods_count' => 0,
            ];
        }

        return [
            'exists' => true,
            'calculated_at' => $cachedData['calculated_at'] ?? null,
            'goods_count' => count($cachedData['goods'] ?? []),
            'shop_settings' => $cachedData['shop_settings'] ?? null,
        ];
    }

    public function getShopsWithCache(): array
    {
        $shops = Shop::all();
        $result = [];
        
        foreach ($shops as $shop) {
            $info = $this->getCacheInfo($shop);
            if ($info['exists']) {
                $result[] = [
                    'shop' => $shop,
                    'cache_info' => $info,
                ];
            }
        }
        
        return $result;
    }

    private function processCachedData(array $goodData, array $dates, Good $good, Shop $shop): array
    {
        // Фильтруем salesData по запрошенным датам
        $filteredSalesData = [];
        foreach ($dates as $date) {
            if (isset($goodData['salesData'][$date])) {
                $filteredSalesData[$date] = $goodData['salesData'][$date];
            }
        }

        // Получаем заметки (не кэшируем, так как могут меняться)
        $notesData = $this->getNotesData($good, $shop, $dates);

        return [
            'goodId' => $good->id,
            'salesData' => $filteredSalesData,
            'monthlyTotals' => $goodData['monthlyTotals'] ?? [],
            'prcentColumn' => $goodData['prcentColumn'] ?? [],
            'salesByWarehouse' => $goodData['salesByWarehouse'] ?? [],
            'notesData' => $notesData,
            'subRowsMetadata' => $this->getSubRowsMetadata(),
        ];
    }

    private function getNotesData(Good $good, Shop $shop, array $dates): array
    {
        $notesData = [];

        $viewId = $shop->workSpaces->first()->view_id ?? 2;

        foreach ($dates as $date) {
            $noteKey = [
                'good_id' => $good->id,
                'view_id' => $viewId,
                'date' => $date
            ];

            $noteExists = Note::where($noteKey)->exists();
            $notesData[$date] = $noteExists;
        }

        return $notesData;
    }

    private function getSubRowsMetadata(): array
    {
        return [
            ['name' => 'Заказы шт.', 'type' => 'orders_count'],
            ['name' => 'Рекл, т.р.', 'type' => 'advertising_costs'],
            ['name' => 'Приб, т.р.', 'type' => 'orders_profit'],
            ['name' => 'Цена до СПП', 'type' => 'price_with_disc'],
            ['name' => 'Цена после СПП', 'type' => 'finished_price'],
            ['name' => 'СПП %', 'type' => 'spp'],
            ['name' => 'Заказы т.р. до СПП', 'type' => 'orders_sum_rub'],
            ['name' => 'Заказы т.р. после СПП', 'type' => 'orders_sum_rub_after_spp'],
            ['name' => 'Заметки', 'type' => 'notes'],
            ['name' => 'ДРР общ. %', 'type' => 'drr_common'],
            ['name' => 'Выкупы, т.р. до СПП', 'type' => 'buyouts_sum_rub'],
            ['name' => '% выкупа', 'type' => 'buyout_percent'],
            ['name' => 'Приб по фин. отчету, т.р.', 'type' => 'profit_without_ads'],
            ['name' => 'Клики всего', 'type' => 'open_card_count'],
            ['name' => 'Клики не рекл', 'type' => 'no_ad_clicks'],
            ['name' => 'Корзины', 'type' => 'add_to_cart_count'],
            ['name' => 'Конв клик-корз', 'type' => 'add_to_cart_conversion'],
            ['name' => 'Конв корз-заказ', 'type' => 'cart_to_order_conversion'],
            ['name' => 'АРК CPM', 'type' => 'aac_cpm'],
            ['name' => 'АРК Показы', 'type' => 'aac_views'],
            ['name' => 'АРК Клики', 'type' => 'aac_clicks'],
            ['name' => 'АРК Затраты, т.р.', 'type' => 'aac_sum'],
            ['name' => 'АРК Зак по рекл', 'type' => 'aac_orders'],
            ['name' => 'АРК CTR', 'type' => 'aac_ctr'],
            ['name' => 'АРК CPO', 'type' => 'aac_cpo'],
            ['name' => 'Аук. CPM', 'type' => 'auc_cpm'],
            ['name' => 'Аук. Показы', 'type' => 'auc_views'],
            ['name' => 'Аук. Клики', 'type' => 'auc_clicks'],
            ['name' => 'Аук. Затраты, т.р.', 'type' => 'auc_sum'],
            ['name' => 'Аук. Зак по рекл', 'type' => 'auc_orders'],
            ['name' => 'Аук. CTR', 'type' => 'auc_ctr'],
            ['name' => 'Аук CPO', 'type' => 'auc_cpo'],
            ['name' => 'Зак. по рекл', 'type' => 'ad_orders'],
            ['name' => 'Зак. не по рекл', 'type' => 'no_ad_orders'],
            ['name' => 'Зак. этого с др. РК', 'type' => 'assoc_orders_from_other'],
            ['name' => 'Зак. с РК др. sku', 'type' => 'assoc_orders_from_this'],
        ];
    }
}