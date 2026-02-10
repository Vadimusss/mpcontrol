<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Shop;
use App\Models\Good;
use App\Models\SalesFunnel;
use App\Models\WbAnalyticsV3ProductsHistory;
use App\Models\Note;
use App\Models\StocksAndOrders;
use Carbon\Carbon;
use Throwable;

class GenerateGoodDetailsCacheJobOptimized implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public $timeout = 1200,
        public $tries = 1,
    ) {
        $this->shop = $shop;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $goods = Good::where('shop_id', $this->shop->id)
            ->with([
                'nsi:good_id,cost_with_taxes',
                'sizes:good_id,price',
                'wbListGoodRow:good_id,discount',
            ])
            ->get(['id', 'nm_id']);

        $total = $goods->count();
        
        if ($total === 0) {
            Log::info("No goods found for shop {$this->shop->id}");
            return;
        }

        $goodIds = $goods->pluck('id')->toArray();
        $nmIds = $goods->pluck('nm_id')->toArray();
        
        $totalsStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
        
        $salesFunnelData = SalesFunnel::whereIn('good_id', $goodIds)
            ->where('date', '>=', $totalsStartDate)
            ->orderBy('good_id')
            ->orderBy('date')
            ->get()
            ->groupBy('good_id');
        
        $conversionData = WbAnalyticsV3ProductsHistory::whereIn('good_id', $goodIds)
            ->where('date', '>=', $totalsStartDate)
            ->select('good_id', 'date', 'add_to_cart_conversion', 'cart_to_order_conversion')
            ->get()
            ->groupBy('good_id')
            ->map(function ($items) {
                $map = [];
                foreach ($items as $item) {
                    $map[$item->date] = [
                        'add_to_cart_conversion' => $item->add_to_cart_conversion ?? 0,
                        'cart_to_order_conversion' => $item->cart_to_order_conversion ?? 0
                    ];
                }
                return $map;
            });
        
        $warehouses = [
            'elektrostal' => 'Электросталь',
            'tula' => 'Тула',
            'nevinnomyssk' => 'Невинномысск',
            'krasnodar' => 'Краснодар',
            'kazan' => 'Казань'
        ];

        $warehouseSalesData = StocksAndOrders::where('shop_id', $this->shop->id)
            ->whereIn('nm_id', $nmIds)
            ->where('date', '>=', $totalsStartDate)
            ->whereIn('warehouse_name', array_values($warehouses))
            ->selectRaw('nm_id, warehouse_name, SUM(orders_count) as total_orders')
            ->groupBy('nm_id', 'warehouse_name')
            ->get()
            ->groupBy('nm_id')
            ->map(function ($items) use ($warehouses) {
                $result = [];
                foreach ($warehouses as $key => $name) {
                    $result[$key] = $items->firstWhere('warehouse_name', $name)?->total_orders ?? 0;
                }
                return $result;
            });

        $shopData = [
            'goods' => [],
            'shop_settings' => [
                'commission' => $this->shop->settings['commission'] ?? null,
                'logistics' => $this->shop->settings['logistics'] ?? null,
            ],
            'calculated_at' => now()->toDateTimeString(),
        ];

        $processed = 0;
        foreach ($goods as $good) {
            $goodData = $this->calculateGoodData(
                $good, 
                $salesFunnelData[$good->id] ?? collect(),
                $conversionData[$good->id] ?? [],
                $warehouseSalesData[$good->nm_id] ?? []
            );
            
            if ($goodData) {
                $shopData['goods'][$good->id] = $goodData;
            }
            
            $processed++;

            if ($processed % 100 === 0) {
                Log::info("GoodDetails cache progress for shop {$this->shop->id}: {$processed}/{$total}");
            }
        }

        $cacheKey = "good_details_cache:shop_{$this->shop->id}";
        Cache::put($cacheKey, $shopData, 86400);

        $duration = microtime(true) - $startTime;
        Log::info("GoodDetails cache generated for shop {$this->shop->id} in {$duration}s, processed {$processed} goods, cached " . count($shopData['goods']) . " goods");
    }

    private function calculateGoodData(
        Good $good, 
        $salesFunnelRows, 
        array $conversionMap, 
        array $warehouseSales
    ): ?array {
        $shop = $this->shop;
        $commission = $shop->settings['commission'] ?? null;
        $logistics = $shop->settings['logistics'] ?? null;

        $dates = collect(range(0, 29))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->all();

        $costWithTaxes = $good->nsi->cost_with_taxes ?? null;

        $salesData = [];
        $monthlyTotals = [
            'spp' => [],
            'drr_common' => [],
            'buyout_percent' => [],
            'add_to_cart_conversion' => [],
            'cart_to_order_conversion' => [],
        ];

        $monthlyFields = [
            'orders_count', 'advertising_costs', 'orders_profit', 'price_with_disc',
            'finished_price', 'orders_sum_rub', 'orders_sum_rub_after_spp',
            'buyouts_sum_rub', 'profit_without_ads', 'buyouts_count',
            'open_card_count', 'add_to_cart_count', 'aac_views', 'aac_clicks',
            'aac_sum', 'aac_orders', 'auc_views', 'auc_clicks', 'auc_sum',
            'auc_orders', 'assoc_orders_from_other', 'assoc_orders_from_this',
            'no_ad_clicks', 'ad_orders', 'no_ad_orders'
        ];

        foreach ($monthlyFields as $field) {
            $monthlyTotals[$field] = 0;
        }

        foreach ($salesFunnelRows as $row) {
            $conversion = $conversionMap[$row->date] ?? [
                'add_to_cart_conversion' => 0,
                'cart_to_order_conversion' => 0
            ];

            $ordersProfit = $this->calculateProfit(
                $row->orders_sum_rub,
                $row->orders_count,
                $row->advertising_costs,
                $costWithTaxes,
                $commission,
                $logistics
            );

            $advertisingCosts = $row->advertising_costs == 0 ? 0 : round($row->advertising_costs / 1000, 1);
            $ordersSumRubAfterSpp = ($row->orders_count == 0 || $row->finished_price == 0) ? 0 : round(($row->orders_count * $row->finished_price));
            $spp = ($row->price_with_disc == 0 || $row->finished_price == 0) ? 0 : round(($row->price_with_disc - $row->finished_price) / $row->price_with_disc * 100);
            $drrCommon = ($advertisingCosts == 0 || $ordersSumRubAfterSpp == 0) ? 0 : round($advertisingCosts / ($ordersSumRubAfterSpp / 1000) * 100);

            $salesData[$row->date] = [
                'orders_count' => (int) $row->orders_count,
                'advertising_costs' => (float) $advertisingCosts,
                'orders_profit' => (float) ($ordersProfit == 0 ? 0 : $ordersProfit / 1000 * -1),
                'price_with_disc' => (int) $row->price_with_disc == 0 ? 0 : round($row->price_with_disc),
                'spp' => (int) $spp,
                'finished_price' => (int) $row->finished_price == 0 ? 0 : round($row->finished_price),
                'orders_sum_rub' => (int) $row->orders_sum_rub == 0 ? 0 : round($row->orders_sum_rub / 1000),
                'orders_sum_rub_after_spp' => (float) $ordersSumRubAfterSpp / 1000,
                'isHighlighted' => $row->advertising_costs != 0 && $row->advertising_costs > 100,
                'buyouts_sum_rub' => (int) $row->buyouts_sum_rub == 0 ? 0 : round($row->buyouts_sum_rub / 1000),
                'drr_common' => (int) $drrCommon,
                'buyout_percent' => (int) $row->buyout_percent == 0 ? 0 : $row->buyout_percent,
                'profit_without_ads' => (float) $row->profit_without_ads == 0 ? 0 : $row->profit_without_ads / 1000,
                'open_card_count' => (int) $row->open_card_count == 0 ? 0 : $row->open_card_count,
                'no_ad_clicks' => (int) ($row->aac_clicks == 0 || $row->auc_clicks == 0) ? 0 : $row->open_card_count - ($row->aac_clicks + $row->auc_clicks),
                'add_to_cart_count' => (int) $row->add_to_cart_count == 0 ? 0 : $row->add_to_cart_count,
                'add_to_cart_conversion' => $conversion['add_to_cart_conversion'] == 0 ? 0 : $conversion['add_to_cart_conversion'],
                'cart_to_order_conversion' => $conversion['cart_to_order_conversion'] == 0 ? 0 : $conversion['cart_to_order_conversion'],
                'aac_cpm' => $row->aac_cpm == 0 ? 0 : round($row->aac_cpm),
                'aac_views' => (int) $row->aac_views == 0 ? 0 : $row->aac_views,
                'aac_clicks' => (int) $row->aac_clicks == 0 ? 0 : $row->aac_clicks,
                'aac_sum' => (float) $row->aac_sum == 0 ? 0 : $row->aac_sum / 1000,
                'aac_orders' => $row->aac_orders == 0 ? 0 : $row->aac_orders,
                'aac_ctr' => (float) $this->calculateCtr($row->aac_views, $row->aac_clicks),
                'aac_cpo' => ($row->aac_sum == 0 || $row->aac_orders == 0) ? 0 : $row->aac_sum / $row->aac_orders,
                'auc_cpm' => $row->auc_cpm == 0 ? 0 : round($row->auc_cpm),
                'auc_views' => (int) $row->auc_views == 0 ? 0 : $row->auc_views,
                'auc_clicks' => (int) $row->auc_clicks == 0 ? 0 : $row->auc_clicks,
                'auc_sum' => (float) $row->auc_sum == 0 ? 0 : $row->auc_sum / 1000,
                'auc_orders' => (int) $row->auc_orders == 0 ? 0 : $row->auc_orders,
                'auc_ctr' => (float) $this->calculateCtr($row->auc_views, $row->auc_clicks),
                'auc_cpo' => ($row->auc_sum == 0 || $row->auc_orders == 0) ? 0 : $row->auc_sum / $row->auc_orders,
                'ad_orders' => (int) ($row->auc_orders == 0 || $row->aac_orders == 0) ? 0 : $row->auc_orders + $row->aac_orders,
                'no_ad_orders' => (int) (($row->auc_orders == 0 || $row->aac_orders == 0) && $row->orders_count == 0) ?
                    0 : $row->orders_count - ($row->auc_orders + $row->aac_orders),
                'assoc_orders_from_other' => (int) $row->assoc_orders_from_other == 0 ? 0 : $row->assoc_orders_from_other,
                'assoc_orders_from_this' => (int) $row->assoc_orders_from_this == 0 ? 0 : $row->assoc_orders_from_this,
            ];

            $this->accumulateMonthlyTotals($row->date, $monthlyTotals, $row, $ordersProfit, $ordersSumRubAfterSpp, $spp, $drrCommon, $conversion);
        }

        $filledSalesData = [];
        foreach ($dates as $date) {
            $filledSalesData[$date] = $salesData[$date] ?? [
                'orders_count' => 0,
                'advertising_costs' => 0,
                'orders_profit' => 0,
                'price_with_disc' => 0,
                'spp' => 0,
                'finished_price' => 0,
                'orders_sum_rub' => 0,
                'orders_sum_rub_after_spp' => 0,
                'isHighlighted' => false,
                'buyouts_sum_rub' => 0,
                'drr_common' => 0,
                'buyout_percent' => 0,
                'profit_without_ads' => 0,
                'open_card_count' => 0,
                'no_ad_clicks' => 0,
                'add_to_cart_count' => 0,
                'add_to_cart_conversion' => 0,
                'cart_to_order_conversion' => 0,
                'aac_cpm' => 0,
                'aac_views' => 0,
                'aac_clicks' => 0,
                'aac_sum' => 0,
                'aac_orders' => 0,
                'aac_ctr' => 0,
                'aac_cpo' => 0,
                'auc_cpm' => 0,
                'auc_views' => 0,
                'auc_clicks' => 0,
                'auc_sum' => 0,
                'auc_orders' => 0,
                'auc_ctr' => 0,
                'auc_cpo' => 0,
                'ad_orders' => 0,
                'no_ad_orders' => 0,
                'assoc_orders_from_other' => 0,
                'assoc_orders_from_this' => 0,
            ];
        }

        $preparedMonthlyTotals = $this->prepareMonthlyTotals($monthlyTotals);
        $percentData = $this->calculatePercentData($preparedMonthlyTotals);

        return [
            'salesData' => $filledSalesData,
            'monthlyTotals' => $preparedMonthlyTotals,
            'prcentColumn' => $percentData,
            'salesByWarehouse' => $warehouseSales,
        ];
    }

    private function accumulateMonthlyTotals($processedDate, &$totals, $row, $ordersProfit, $ordersSumRubAfterSpp, $spp, $drrCommon, $conversion): void
    {
        $fields = [
            'orders_count' => $row->orders_count,
            'advertising_costs' => $row->advertising_costs,
            'orders_profit' => $ordersProfit,
            'price_with_disc' => $row->price_with_disc,
            'finished_price' => $row->finished_price,
            'orders_sum_rub' => $row->orders_sum_rub,
            'orders_sum_rub_after_spp' => $ordersSumRubAfterSpp,
            'buyouts_sum_rub' => $row->buyouts_sum_rub,
            'profit_without_ads' => $row->profit_without_ads,
            'buyouts_count' => $row->buyouts_count,
            'open_card_count' => $row->open_card_count,
            'add_to_cart_count' => $row->add_to_cart_count,
            'aac_views' => $row->aac_views,
            'aac_clicks' => $row->aac_clicks,
            'aac_sum' => $row->aac_sum,
            'aac_orders' => $row->aac_orders,
            'auc_views' => $row->auc_views,
            'auc_clicks' => $row->auc_clicks,
            'auc_sum' => $row->auc_sum,
            'auc_orders' => $row->auc_orders,
            'assoc_orders_from_other' => $row->assoc_orders_from_other,
            'assoc_orders_from_this' => $row->assoc_orders_from_this,
        ];

        foreach ($fields as $field => $value) {
            if (is_numeric($value)) {
                $totals[$field] += $value;
            }
        }

        $date = Carbon::parse($processedDate);
        if ($date->lt(Carbon::now()->subDays(9))) {
            $totals['buyout_percent'][] = $row->buyout_percent;
        }

        if (is_numeric($spp) && $spp > 0) {
            $totals['spp'][] = $spp;
        }

        if (is_numeric($drrCommon) && $drrCommon > 0) {
            $totals['drr_common'][] = $drrCommon;
        }

        if (is_numeric($conversion['add_to_cart_conversion']) && $conversion['add_to_cart_conversion'] > 0) {
            $totals['add_to_cart_conversion'][] = $conversion['add_to_cart_conversion'];
        }

        if (is_numeric($conversion['cart_to_order_conversion']) && $conversion['cart_to_order_conversion'] > 0) {
            $totals['cart_to_order_conversion'][] = $conversion['cart_to_order_conversion'];
        }

        $noAdClicks = ($row->aac_clicks != 0 || $row->auc_clicks != 0) ? $row->open_card_count - ($row->aac_clicks + $row->auc_clicks) : 0;
        if (is_numeric($noAdClicks)) {
            $totals['no_ad_clicks'] += $noAdClicks;
        }

        $adOrders = ($row->auc_orders != 0 || $row->aac_orders != 0) ? $row->auc_orders + $row->aac_orders : 0;
        if (is_numeric($adOrders)) {
            $totals['ad_orders'] += $adOrders;
        }

        $noAdOrders = (($row->auc_orders != 0 || $row->aac_orders != 0) && $row->orders_count != 0) ?
            $row->orders_count - ($row->auc_orders + $row->aac_orders) : 0;
        if (is_numeric($noAdOrders)) {
            $totals['no_ad_orders'] += $noAdOrders;
        }
    }

    private function prepareMonthlyTotals(array $monthlyTotals): array
    {
        $prepared = $monthlyTotals;

        $prepared['advertising_costs'] = $monthlyTotals['advertising_costs'] == 0 ?
            0 : $monthlyTotals['advertising_costs'];

        $prepared['orders_profit'] = $monthlyTotals['orders_profit'] == 0 ?
            0 : $monthlyTotals['orders_profit'];

        $prepared['price_with_disc'] = ($monthlyTotals['price_with_disc'] == 0 || $monthlyTotals['orders_count'] == 0) ?
            0 : $monthlyTotals['orders_sum_rub'] / $monthlyTotals['orders_count'];

        $prepared['finished_price'] = ($monthlyTotals['finished_price'] == 0 || $monthlyTotals['orders_count'] == 0) ?
            0 : $monthlyTotals['orders_sum_rub_after_spp'] / $monthlyTotals['orders_count'];

        $prepared['spp'] = count($monthlyTotals['spp']) == 0 ? 0 : array_sum($monthlyTotals['spp']) / count($monthlyTotals['spp']);

        $prepared['drr_common'] = count($monthlyTotals['drr_common']) == 0 ? 0 : array_sum($monthlyTotals['drr_common']) / count($monthlyTotals['drr_common']);

        $prepared['buyout_percent'] = count($monthlyTotals['buyout_percent']) === 0 ? 0 : array_sum($monthlyTotals['buyout_percent']) / count($monthlyTotals['buyout_percent']);

        $prepared['add_to_cart_conversion'] = count($monthlyTotals['add_to_cart_conversion']) === 0 ?
            0 : array_sum($monthlyTotals['add_to_cart_conversion']) / count($monthlyTotals['add_to_cart_conversion']);

        $prepared['cart_to_order_conversion'] = count($monthlyTotals['cart_to_order_conversion']) === 0 ?
            0 : array_sum($monthlyTotals['cart_to_order_conversion']) / count($monthlyTotals['cart_to_order_conversion']);

        $prepared['orders_sum_rub'] = $monthlyTotals['orders_sum_rub'] == 0 ?
            0 : round($monthlyTotals['orders_sum_rub']);

        $prepared['orders_sum_rub_after_spp'] = $monthlyTotals['orders_sum_rub_after_spp'] == 0 ?
            0 : $monthlyTotals['orders_sum_rub_after_spp'];

        $prepared['aac_cpm'] = $monthlyTotals['aac_sum'] == 0 || $monthlyTotals['aac_views'] == 0 ?
            0 : $monthlyTotals['aac_sum'] / $monthlyTotals['aac_views'] * 1000;

        $prepared['aac_sum'] = $monthlyTotals['aac_sum'] == 0 ?
            0 : round($monthlyTotals['aac_sum'] / 1000, 1);

        $prepared['aac_ctr'] = $monthlyTotals['aac_views'] == 0 || $monthlyTotals['aac_clicks'] == 0 ?
            0 : $monthlyTotals['aac_clicks'] / $monthlyTotals['aac_views'] * 100;

        $prepared['aac_cpo'] = $monthlyTotals['finished_price'] == 0 || $monthlyTotals['aac_orders'] == 0 ?
            0 : $monthlyTotals['aac_sum'] / $monthlyTotals['aac_orders'];

        $prepared['auc_cpm'] = $monthlyTotals['auc_sum'] == 0 || $monthlyTotals['auc_views'] == 0 ?
            0 : $monthlyTotals['auc_sum'] / $monthlyTotals['auc_views'] * 1000;

        $prepared['auc_sum'] = $monthlyTotals['auc_sum'] == 0 ?
            0 : round($monthlyTotals['auc_sum'] / 1000, 1);

        $prepared['auc_ctr'] = $monthlyTotals['auc_views'] == 0 || $monthlyTotals['auc_clicks'] == 0 ?
            0 : $monthlyTotals['auc_clicks'] / $monthlyTotals['auc_views'] * 100;

        $prepared['auc_cpo'] = $monthlyTotals['auc_sum'] == 0 || $monthlyTotals['auc_orders'] == 0 ?
            0 : $monthlyTotals['auc_sum'] / $monthlyTotals['auc_orders'];

        return $prepared;
    }

    private function calculatePercentData(array $monthlyTotals): array
    {
        $result = [];

        $result['advertising_costs'] = $monthlyTotals['advertising_costs'] == 0 || $monthlyTotals['orders_sum_rub_after_spp'] == 0 ?
            0 : $monthlyTotals['advertising_costs'] / $monthlyTotals['orders_sum_rub_after_spp'] * 100;

        $result['orders_profit'] = $monthlyTotals['orders_profit'] == 0 || $monthlyTotals['orders_sum_rub_after_spp'] == 0 ?
            0 : $monthlyTotals['orders_profit'] / $monthlyTotals['orders_sum_rub_after_spp'] * 100;

        $result['no_ad_clicks'] = $monthlyTotals['no_ad_clicks'] == 0 || $monthlyTotals['open_card_count'] == 0 ?
            0 : $monthlyTotals['no_ad_clicks'] / $monthlyTotals['open_card_count'] * 100;

        $adViewsTotal = $monthlyTotals['aac_views'] + $monthlyTotals['auc_views'];

        $result['aac_views'] = $adViewsTotal == 0 ? 0 : $monthlyTotals['aac_views'] / $adViewsTotal * 100;

        $result['auc_views'] = $adViewsTotal == 0 ? 0 : $monthlyTotals['auc_views'] / $adViewsTotal * 100;

        $adClicksTotal = $monthlyTotals['aac_clicks'] + $monthlyTotals['auc_clicks'];

        $result['aac_clicks'] = $adClicksTotal == 0 ? 0 : $monthlyTotals['aac_clicks'] / $adClicksTotal * 100;

        $result['auc_clicks'] = $adClicksTotal == 0 ? 0 : $monthlyTotals['auc_clicks'] / $adClicksTotal * 100;

        $adSumTotal = $monthlyTotals['aac_sum'] + $monthlyTotals['auc_sum'];

        $result['aac_sum'] = $adSumTotal == 0 ? 0 : $monthlyTotals['aac_sum'] / $adSumTotal * 100;

        $result['auc_sum'] = $adSumTotal == 0 ? 0 : $monthlyTotals['auc_sum'] / $adSumTotal * 100;

        $result['aac_orders'] = $monthlyTotals['orders_count'] == 0 ? 0 : $monthlyTotals['aac_orders'] / $monthlyTotals['orders_count'] * 100;

        $result['auc_orders'] = $monthlyTotals['orders_count'] == 0 ? 0 : $monthlyTotals['auc_orders'] / $monthlyTotals['orders_count'] * 100;

        $result['aac_cpo'] = $monthlyTotals['aac_sum'] == 0 || $monthlyTotals['aac_orders'] == 0 || $monthlyTotals['finished_price'] == 0 ?
            0 : $monthlyTotals['aac_sum'] / $monthlyTotals['aac_orders'] / $monthlyTotals['finished_price'];

        $result['aac_cpo'] = $monthlyTotals['finished_price'] == 0 ? 0 : $monthlyTotals['aac_cpo'] / $monthlyTotals['finished_price'] * 100;

        $result['auc_cpo'] = $monthlyTotals['finished_price'] == 0 ? 0 : $monthlyTotals['auc_cpo'] / $monthlyTotals['finished_price'] * 100;

        $result['ad_orders'] = $monthlyTotals['orders_count'] == 0 ? 0 : $monthlyTotals['ad_orders'] / $monthlyTotals['orders_count'] * 100;

        $result['no_ad_orders'] = $monthlyTotals['orders_count'] == 0 ? 0 : $monthlyTotals['no_ad_orders'] / $monthlyTotals['orders_count'] * 100;

        return $result;
    }

    private function calculateCtr($views, $clicks): string
    {
        if (!is_numeric($views) || !is_numeric($clicks) || $views <= 0 || $clicks <= 0) {
            return 0;
        }
        $ctr = ($clicks / $views) * 100;
        return $ctr;
    }

    private function calculateProfit($sum, $count, $advertising_costs, $costWithTaxes, $commission, $logistics): string
    {
        if (
            $sum == null || $commission == null ||
            $logistics == null || $advertising_costs == null ||
            $costWithTaxes == null
        ) {
            return 0;
        }

        $commissionPercent = $commission / 100;

        return round($sum
            - ($sum * $commissionPercent)
            - ($count * $logistics)
            - $advertising_costs
            - ($count * $costWithTaxes));
    }

    public function failed(?Throwable $exception): void
    {
        Log::error("GenerateGoodDetailsCacheJobOptimized failed for shop {$this->shop->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
