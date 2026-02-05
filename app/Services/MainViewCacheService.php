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
            return [];
        }

        $viewSettings = json_decode($workSpace->viewSettings->settings);
        $days = $viewSettings->days ?? 14;

        return $this->processCachedData($cachedData, $goodIds, $days, $shop);
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

    private function processCachedData(array $cachedData, array $goodIds, int $days, Shop $shop): array
    {
        $goodsMap = [];
        foreach ($cachedData['goods'] as $goodData) {
            $goodsMap[$goodData['id']] = $goodData;
        }

        $filteredGoods = array_filter($cachedData['goods'], function ($goodData) use ($goodIds) {
            return in_array($goodData['id'], $goodIds);
        });

        $processedGoods = array_map(function ($goodData) use ($days) {
            if (isset($goodData['orders_count']) && is_array($goodData['orders_count'])) {
                $goodData['orders_count'] = array_slice($goodData['orders_count'], 0, $days, true);
            }
            return $goodData;
        }, $filteredGoods);

        return array_values($processedGoods);
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
}