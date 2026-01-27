<?php

namespace App\Services\ViewHandlers;

use App\Models\Good;
use App\Models\Shop;
use App\Models\WbAnalyticsV3ProductsHistory;
use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class GoodDetailsModalHandler
{
    public function prepareGoodDetailsData(Good $good, Shop $shop, array $dates): array
    {
        $commission = $shop->settings['commission'] ?? null;
        $logistics = $shop->settings['logistics'] ?? null;

        $totalsStartDate = Carbon::now()->subDays(30)->format('Y-m-d');

        $shop->load('workSpaces');

        $good->load([
            'nsi:good_id,name,variant,fg_1,cost_with_taxes',
            'sizes:good_id,price',
            'wbListGoodRow:good_id,discount',
            'salesFunnel' => function ($q) use ($dates, $totalsStartDate) {
                $q->where(function ($query) use ($dates, $totalsStartDate) {
                    $query->whereIn('date', $dates)
                        ->orWhere('date', '>=', $totalsStartDate);
                })->orderBy('date');
            },
            'wbAnalyticsV3ProductsHistory' => function ($q) use ($dates) {
                $q->select('good_id', 'date', 'add_to_cart_conversion', 'cart_to_order_conversion')
                    ->whereIn('date', $dates);
            }
        ]);

        $notesData = $this->getNotesData($good, $shop, $dates);

        $conversionMap = [];
        foreach ($good->wbAnalyticsV3ProductsHistory as $conversionData) {
            $conversionMap[$conversionData->date] = [
                'add_to_cart_conversion' => $conversionData->add_to_cart_conversion ?? 0,
                'cart_to_order_conversion' => $conversionData->cart_to_order_conversion ?? 0
            ];
        }

        $salesData = [];
        $monthlyTotals = [
            'spp' => [],
            'drr_common' => [],
        ];

        foreach ($good->salesFunnel as $row) {
            $conversion = $conversionMap[$row->date] ?? [
                'add_to_cart_conversion' => 0,
                'cart_to_order_conversion' => 0
            ];

            $ordersProfit = $this->calculateProfit($row->orders_sum_rub, $row->orders_count, $row->advertising_costs, $good->nsi, $commission, $logistics);
            // $buyoutsProfit = $this->calculateProfit($row->buyouts_sum_rub, $row->buyouts_count, $row->advertising_costs, $good->nsi, $commission, $logistics);

            $advertisingCosts = $row->advertising_costs == 0 ? 0 : round($row->advertising_costs / 1000, 1);
            $ordersSumRubAfterSpp = ($row->orders_count == 0 || $row->finished_price == 0) ? 0 : round(($row->orders_count * $row->finished_price));
            $spp = ($row->price_with_disc == 0 || $row->finished_price == 0) ? 0 : round(($row->price_with_disc - $row->finished_price) / $row->price_with_disc * 100);
            $drrCommon = ($advertisingCosts == 0 || $ordersSumRubAfterSpp == 0) ? 0 : round($advertisingCosts / ($ordersSumRubAfterSpp / 1000) * 100);

            if (in_array($row->date, $dates)) {
                $salesData[$row->date] = [
                    'orders_count' => (int) $row->orders_count,
                    'advertising_costs' => (float) $advertisingCosts,
                    'orders_profit' => (float) $ordersProfit == 0 ? 0 : $ordersProfit / 1000 * -1,
                    'price_with_disc' => (int) $row->price_with_disc == 0 ? 0 : round($row->price_with_disc),
                    'spp' => (int) $spp,
                    'finished_price' => (int) $row->finished_price == 0 ? 0 : round($row->finished_price),
                    'orders_sum_rub' => (int) $row->orders_sum_rub == 0 ? 0 : round($row->orders_sum_rub / 1000),
                    'orders_sum_rub_after_spp' => (float) $ordersSumRubAfterSpp / 1000,
                    // 'buyouts_sum_rub' => $row->buyouts_sum_rub == 0 ? '' : round($row->buyouts_sum_rub / 1000),
                    'isHighlighted' => $row->advertising_costs != 0 && $row->advertising_costs > 100,
                    // 'buyouts_count' =>(int) $row->buyouts_count == 0 ? '' : $row->buyouts_count,
                    // 'buyouts_profit' => (float) $buyoutsProfit == 0 ? '' : round($buyoutsProfit / 1000 * -1, 1),
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
                    // 'aac_cpc' => (float) $this->calculateCpc($row->aac_sum, $row->aac_clicks),
                    'auc_cpm' => $row->auc_cpm == 0 ? 0 : round($row->auc_cpm),
                    'auc_views' => (int) $row->auc_views == 0 ? 0 : $row->auc_views,
                    'auc_clicks' => (int) $row->auc_clicks == 0 ? 0 : $row->auc_clicks,
                    'auc_sum' => (float) $row->auc_sum == 0 ? 0 : $row->auc_sum / 1000,
                    'auc_orders' => (int) $row->auc_orders == 0 ? 0 : $row->auc_orders,
                    'auc_ctr' => (float) $this->calculateCtr($row->auc_views, $row->auc_clicks),
                    'auc_cpo' => ($row->auc_sum == 0 || $row->auc_orders == 0) ? 0 : $row->auc_sum / $row->auc_orders,
                    // 'auc_cpc' => (float) $this->calculateCpc($row->auc_sum, $row->auc_clicks),
                    'ad_orders' => (int) ($row->auc_orders == 0 || $row->aac_orders == 0) ? 0 : $row->auc_orders + $row->aac_orders,
                    'no_ad_orders' => (int) (($row->auc_orders == 0 || $row->aac_orders == 0) && $row->orders_count == 0) ?
                        0 : $row->orders_count - ($row->auc_orders + $row->aac_orders),
                    'assoc_orders_from_other' => (int) $row->assoc_orders_from_other == 0 ? 0 : $row->assoc_orders_from_other,
                    'assoc_orders_from_this' => (int) $row->assoc_orders_from_this == 0 ? 0 : $row->assoc_orders_from_this,
                ];
            }

            $this->accumulateMonthlyTotals($row->date, $monthlyTotals, $row, $ordersProfit, $ordersSumRubAfterSpp, $spp, $drrCommon, $conversion);
        }

        $salesByWarehouse = $this->calculateSalesByWarehouse($shop, $totalsStartDate, [
            'elektrostal' => 'Электросталь',
            'tula' => 'Тула',
            'nevinnomyssk' => 'Невинномысск',
            'krasnodar' => 'Краснодар',
            'kazan' => 'Казань'
        ]);

        return [
            'goodId' => $good->id,
            'salesData' => $salesData,
            'monthlyTotals' => $this->prepareMonthlyTotals($monthlyTotals),
            'prcentColumn' => $this->calculatePercentData($monthlyTotals),
            'salesByWarehouse' => $salesByWarehouse->get($good->nm_id, []),
            'notesData' => $notesData,
            'subRowsMetadata' => [
                ['name' => 'Заказы шт.', 'type' => 'orders_count'],
                ['name' => 'Рекл, т.р.', 'type' => 'advertising_costs'],
                ['name' => 'Приб, т.р.', 'type' => 'orders_profit'],
                ['name' => 'Цена до СПП', 'type' => 'price_with_disc'],
                ['name' => 'Цена после СПП', 'type' => 'finished_price'],
                ['name' => 'СПП %', 'type' => 'spp'],
                ['name' => 'Заказы т.р. до СПП', 'type' => 'orders_sum_rub'],
                ['name' => 'Заказы т.р. после СПП', 'type' => 'orders_sum_rub_after_spp'],
                // ['name' => 'Продажи руб, т.р.', 'type' => 'buyouts_sum_rub'],
                // ['name' => 'Продажи шт', 'type' => 'buyouts_count'],
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
                // ['name' => 'АРК CPC', 'type' => 'aac_cpc'],
                ['name' => 'Аук. CPM', 'type' => 'auc_cpm'],
                ['name' => 'Аук. Показы', 'type' => 'auc_views'],
                ['name' => 'Аук. Клики', 'type' => 'auc_clicks'],
                ['name' => 'Аук. Затраты, т.р.', 'type' => 'auc_sum'],
                ['name' => 'Аук. Зак по рекл', 'type' => 'auc_orders'],
                ['name' => 'Аук. CTR', 'type' => 'auc_ctr'],
                ['name' => 'Аук CPO', 'type' => 'auc_cpo'],
                // ['name' => 'Аук. CPC', 'type' => 'auc_cpc'],
                ['name' => 'Зак. по рекл', 'type' => 'ad_orders'],
                ['name' => 'Зак. не по рекл', 'type' => 'no_ad_orders'],
                ['name' => 'Зак. этого с др. РК', 'type' => 'assoc_orders_from_other'],
                ['name' => 'Зак. с РК др. sku', 'type' => 'assoc_orders_from_this'],
            ]
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
            'aac_cpm' => $row->aac_cpm,
            'aac_views' => $row->aac_views,
            'aac_clicks' => $row->aac_clicks,
            'aac_sum' => $row->aac_sum,
            'aac_orders' => $row->aac_orders,
            'auc_cpm' => $row->auc_cpm,
            'auc_views' => $row->auc_views,
            'auc_clicks' => $row->auc_clicks,
            'auc_sum' => $row->auc_sum,
            'auc_orders' => $row->auc_orders,
        ];

        foreach ($fields as $field => $value) {
            if (is_numeric($value)) {
                $totals[$field] = ($totals[$field] ?? 0) + $value;
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
            $totals['no_ad_clicks'] = ($totals['no_ad_clicks'] ?? 0) + $noAdClicks;
        }

        $adOrders = ($row->auc_orders != 0 || $row->aac_orders != 0) ? $row->auc_orders + $row->aac_orders : 0;
        if (is_numeric($adOrders)) {
            $totals['ad_orders'] = ($totals['ad_orders'] ?? 0) + $adOrders;
        }

        $noAdOrders = (($row->auc_orders != 0 || $row->aac_orders != 0) && $row->orders_count != 0) ?
            $row->orders_count - ($row->auc_orders + $row->aac_orders) : 0;
        if (is_numeric($noAdOrders)) {
            $totals['no_ad_orders'] = ($totals['no_ad_orders'] ?? 0) + $noAdOrders;
        }
    }

    private function prepareMonthlyTotals(array $monthlyTotals): array
    {
        $prepared = $monthlyTotals;

        $prepared['advertising_costs'] = $monthlyTotals['advertising_costs'] == 0 ?
            0 : $monthlyTotals['advertising_costs'];

        $prepared['orders_profit'] = $monthlyTotals['orders_profit'] == 0 ?
            0 : $monthlyTotals['orders_profit'];

        $prepared['price_with_disc'] = ($monthlyTotals['price_with_disc'] == 0 && $monthlyTotals['orders_count'] == 0) ?
            0 : $monthlyTotals['orders_sum_rub'] / $monthlyTotals['orders_count'];

        $prepared['finished_price'] = ($monthlyTotals['finished_price'] == 0 && $monthlyTotals['orders_count'] == 0) ?
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

        $prepared['aac_sum'] = $monthlyTotals['aac_sum'] == 0 ?
            0 : round($monthlyTotals['aac_sum'] / 1000, 1);

        $prepared['auc_sum'] = $monthlyTotals['auc_sum'] == 0 ?
            0 : round($monthlyTotals['auc_sum'] / 1000, 1);

        return $prepared;
    }

    private function calculatePercentData(array $monthlyTotals): array
    {
        $result = [];

        $result['advertising_costs'] = $monthlyTotals['advertising_costs'] == 0 || $monthlyTotals['orders_sum_rub_after_spp'] == 0 ?
            0 : $monthlyTotals['advertising_costs'] / $monthlyTotals['orders_sum_rub_after_spp'] * 100;

        $result['orders_profit'] = $monthlyTotals['orders_profit'] == 0 || $monthlyTotals['orders_sum_rub_after_spp'] == 0 ?
            0 : $monthlyTotals['orders_profit'] / $monthlyTotals['orders_sum_rub_after_spp'] * 100;

        return $result;
    }

    private function calculateSalesByWarehouse(Shop $shop, string $startDate, array $warehouses)
    {
        return $shop->stocksAndOrders()
            ->where('date', '>=', $startDate)
            ->selectRaw('nm_id, warehouse_name, sum(orders_count) as total_orders')
            ->whereIn('warehouse_name', array_values($warehouses))
            ->groupBy('nm_id', 'warehouse_name')
            ->get()
            ->mapToGroups(function ($item) {
                return [$item->nm_id => $item];
            })
            ->map(function ($items) use ($warehouses) {
                $result = [];
                foreach ($warehouses as $key => $name) {
                    $result[$key] = $items->firstWhere('warehouse_name', $name)?->total_orders ?? 0;
                }
                return $result;
            });
    }

    private function calculateCtr($views, $clicks): string
    {
        if (!is_numeric($views) || !is_numeric($clicks) || $views <= 0) {
            return '';
        }
        $ctr = ($clicks / $views) * 100;
        return round($ctr, 2);
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

    private function calculateProfit($sum, $count, $advertising_costs, $nsi, $commission, $logistics): string
    {
        $costWithTaxes = $nsi->cost_with_taxes ?? null;
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
}
