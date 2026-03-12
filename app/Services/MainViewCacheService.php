<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\WorkSpace;
use App\Models\Shop;
use Carbon\Carbon;

class MainViewCacheService
{
    public function getForWorkspace(WorkSpace $workSpace): ?array
    {
        $shop = $workSpace->shop;
        $cacheKey = "main_view_cache:shop_{$shop->id}";

        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return null;
        }

        $goodIds = $workSpace->connectedGoodLists()
            ->with('goods')
            ->get()
            ->flatMap(function ($list) {
                return $list->goods->pluck('id');
            })
            ->unique()
            ->values()
            ->toArray();

        if (empty($goodIds)) {
            return [
                'data' => [
                    'goods' => [],
                    'categorysTotals' => []
                ],
                'calculated_at' => $cachedData['calculated_at'] ?? now(),
                'shop_settings' => $cachedData['shop_settings'] ?? null,
            ];
        }

        $days = 30;

        return $this->processCachedData($cachedData, $goodIds, $days);
    }

    public function clearForShop(Shop $shop): void
    {
        $cacheKey = "main_view_cache:shop_{$shop->id}";
        Cache::forget($cacheKey);
    }

    public function hasCacheForShop(Shop $shop): bool
    {
        $cacheKey = "main_view_cache:shop_{$shop->id}";
        return Cache::has($cacheKey);
    }

    private function processCachedData(array $cachedData, array $goodIds, int $days): array
    {
        $goodsData = $cachedData['data']['goods'] ?? [];
        $categorysTotals = $cachedData['data']['categorysTotals'] ?? [];

        $filteredGoods = array_filter($goodsData, function ($goodData) use ($goodIds) {
            return in_array($goodData['id'], $goodIds);
        });

        $processedGoods = array_map(function ($goodData) use ($days) {
            if (isset($goodData['sales_funnel']) && is_array($goodData['sales_funnel'])) {
                $goodData['sales_funnel'] = array_slice($goodData['sales_funnel'], 0, $days, true);
            }
            return $goodData;
        }, $filteredGoods);

        $filteredCategorysTotals = $this->filterCategorysTotals($categorysTotals, $processedGoods);

        return [
            'goods' => array_values($processedGoods),
            'categorysTotals' => $filteredCategorysTotals,
        ];
    }

    private function filterCategorysTotals(array $categorysTotals, array $processedGoods): array
    {
        if (empty($categorysTotals)) {
            return [];
        }

        $categories = [];
        foreach ($processedGoods as $good) {
            $category = $good['internal_nsi']['fg_1'] ?? 'Без категории';
            $categories[$category] = true;
        }

        $filtered = [];
        foreach ($categorysTotals as $category => $data) {
            if (isset($categories[$category])) {
                $filtered[$category] = $data;
            }
        }

        return $filtered;
    }

    public function getCacheInfo(Shop $shop): array
    {
        $cacheKey = "main_view_cache:shop_{$shop->id}";
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return [
                'exists' => false,
                'calculated_at' => null,
                'goods_count' => 0,
                'categories_count' => 0,
            ];
        }

        $goodsCount = count($cachedData['data']['goods'] ?? []);
        $categoriesCount = count($cachedData['data']['categorysTotals'] ?? []);

        return [
            'exists' => true,
            'calculated_at' => $cachedData['calculated_at'] ?? null,
            'goods_count' => $goodsCount,
            'categories_count' => $categoriesCount,
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
}
