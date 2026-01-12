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
        $monthlyTotals = [];

        foreach ($good->salesFunnel as $row) {
            $conversion = $conversionMap[$row->date] ?? [
                'add_to_cart_conversion' => 0,
                'cart_to_order_conversion' => 0
            ];

            $ordersProfit = $this->calculateProfit($row->orders_sum_rub, $row->orders_count, $row->advertising_costs, $good->nsi, $commission, $logistics);
            // $buyoutsProfit = $this->calculateProfit($row->buyouts_sum_rub, $row->buyouts_count, $row->advertising_costs, $good->nsi, $commission, $logistics);

            $spp = ($row->price_with_disc == 0 || $row->finished_price == 0) ? '' : round(($row->price_with_disc - $row->finished_price) / $row->price_with_disc * 100);
            $advertisingCosts = $row->advertising_costs === 0 ? '' : round($row->advertising_costs / 1000, 1);
            $ordersSumRubAfterSpp = ($row->orders_count === 0 || $row->finished_price == 0) ? '' : round(($row->orders_count * $row->finished_price) / 1000);

                if (in_array($row->date, $dates)) {
                $salesData[$row->date] = [
                    'orders_count' => (int) $row->orders_count === 0 ? '' : $row->orders_count,
                    'advertising_costs' => (float) $advertisingCosts,
                    'orders_profit' => (float) $ordersProfit == 0 ? '' : round($ordersProfit / 1000 * -1, 1),
                    'price_with_disc' => $row->price_with_disc === 0 ? '' : round($row->price_with_disc),
                    'spp' => (int) $spp,
                    'finished_price' => $row->finished_price == 0 ? '' : round($row->finished_price),
                    'orders_sum_rub' => $row->orders_sum_rub === 0 ? '' : round($row->orders_sum_rub / 1000),
                    'orders_sum_rub_after_spp' => (float) $ordersSumRubAfterSpp,
                    // 'buyouts_sum_rub' => $row->buyouts_sum_rub === 0 ? '' : round($row->buyouts_sum_rub / 1000),
                    'isHighlighted' => $row->advertising_costs != 0 && $row->advertising_costs > 100,
                    // 'buyouts_count' =>(int) $row->buyouts_count === 0 ? '' : $row->buyouts_count,
                    // 'buyouts_profit' => (float) $buyoutsProfit == 0 ? '' : round($buyoutsProfit / 1000 * -1, 1),
                    'buyouts_sum_rub' => $row->buyouts_sum_rub === 0 ? '' : round($row->buyouts_sum_rub / 1000),
                    'drr_common' => (int) ($advertisingCosts == '' || $ordersSumRubAfterSpp == '') ? '' : round($advertisingCosts / $ordersSumRubAfterSpp * 100),
                    'buyout_percent' => (int) $row->buyout_percent == 0 ? '' : $row->buyout_percent,
                    'open_card_count' => (int) $row->open_card_count === 0 ? '' : $row->open_card_count,
                    'no_ad_clicks' => (int) ($row->aac_clicks != 0 || $row->auc_clicks != 0) ? $row->open_card_count - ($row->aac_clicks + $row->auc_clicks) : '',
                    'add_to_cart_count' => (int) $row->add_to_cart_count === 0 ? '' : $row->add_to_cart_count,
                    'add_to_cart_conversion' => $conversion['add_to_cart_conversion'] === 0 ? '' : $conversion['add_to_cart_conversion'],
                    'cart_to_order_conversion' => $conversion['cart_to_order_conversion'] === 0 ? '' : $conversion['cart_to_order_conversion'],

                    'aac_cpm' => $row->aac_cpm === 0 ? '' : round($row->aac_cpm),
                    'aac_views' => (int) $row->aac_views === 0 ? '' : $row->aac_views,
                    'aac_clicks' => (int) $row->aac_clicks === 0 ? '' : $row->aac_clicks,
                    'aac_sum' => (float) $row->aac_sum === 0 ? '' : round($row->aac_sum / 1000, 1),
                    'aac_orders' => $row->aac_orders === 0 ? '' : $row->aac_orders,
                    'aac_ctr' => (float) $this->calculateCtr($row->aac_views, $row->aac_clicks),
                    // 'aac_cpc' => (float) $this->calculateCpc($row->aac_sum, $row->aac_clicks),

                    'auc_cpm' => $row->auc_cpm === 0 ? '' : round($row->auc_cpm),
                    'auc_views' => (int) $row->auc_views === 0 ? '' : $row->auc_views,
                    'auc_clicks' => (int) $row->auc_clicks === 0 ? '' : $row->auc_clicks,
                    'auc_sum' => (float) $row->auc_sum === 0 ? '' : round($row->auc_sum / 1000, 1),
                    'auc_orders' => (int) $row->auc_orders === 0 ? '' : $row->auc_orders,
                    'auc_ctr' => (float) $this->calculateCtr($row->auc_views, $row->auc_clicks),
                    // 'auc_cpc' => (float) $this->calculateCpc($row->auc_sum, $row->auc_clicks),

                    'ad_orders' => (int) ($row->auc_orders != 0 || $row->aac_orders != 0) ? $row->auc_orders + $row->aac_orders : '',
                    'no_ad_orders' => (int) (($row->auc_orders != 0 || $row->aac_orders != 0) && $row->orders_count != 0) ?
                        $row->orders_count - ($row->auc_orders + $row->aac_orders) : '',
                    'assoc_orders_from_other' => (int) $row->assoc_orders_from_other === 0 ? '' : $row->assoc_orders_from_other,
                    'assoc_orders_from_this' => (int) $row->assoc_orders_from_this === 0 ? '' : $row->assoc_orders_from_this,
                ];
            }

            if ($row->date >= $totalsStartDate) {
                $this->accumulateMonthlyTotals($monthlyTotals, $row, $ordersProfit/* , $buyoutsProfit */);
            }
        }

        $monthlyTotals['finished_price'] = ($monthlyTotals['finished_price'] == 0 && $monthlyTotals['orders_count'] == 0) ?
            '' : round($monthlyTotals['finished_price'] / $monthlyTotals['orders_count']);

        $monthlyTotals['advertising_costs'] = $monthlyTotals['advertising_costs'] == 0 ?
            '' : round($monthlyTotals['advertising_costs'] / 1000, 1);

        $monthlyTotals['orders_profit'] = $monthlyTotals['orders_profit'] == 0 ?
            '' : round($monthlyTotals['orders_profit'] / 1000 * -1, 1);

        $monthlyTotals['aac_sum'] = $monthlyTotals['aac_sum'] == 0 ?
            '' : round($monthlyTotals['aac_sum'] / 1000, 1);

        $monthlyTotals['auc_sum'] = $monthlyTotals['auc_sum'] == 0 ?
            '' : round($monthlyTotals['auc_sum'] / 1000, 1);

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
            'monthlyTotals' => $monthlyTotals,
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
                ['name' => 'Клики всего', 'type' => 'open_card_count'],
                ['name' => 'Клики не рекл', 'type' => 'no_ad_clicks'],
                ['name' => 'Корзины', 'type' => 'add_to_cart_count'],
                ['name' => 'Конв корз', 'type' => 'add_to_cart_conversion'],
                ['name' => 'Конв заказ', 'type' => 'cart_to_order_conversion'],
                ['name' => 'АРК CPM', 'type' => 'aac_cpm'],
                ['name' => 'АРК Показы', 'type' => 'aac_views'],
                ['name' => 'АРК Клики', 'type' => 'aac_clicks'],
                ['name' => 'АРК Затраты, т.р.', 'type' => 'aac_sum'],
                ['name' => 'АРК Зак по рекл', 'type' => 'aac_orders'],
                ['name' => 'АРК CTR', 'type' => 'aac_ctr'],
                // ['name' => 'АРК CPC', 'type' => 'aac_cpc'],
                ['name' => 'Аук. CPM', 'type' => 'auc_cpm'],
                ['name' => 'Аук. Показы', 'type' => 'auc_views'],
                ['name' => 'Аук. Клики', 'type' => 'auc_clicks'],
                ['name' => 'Аук. Затраты, т.р.', 'type' => 'auc_sum'],
                ['name' => 'Аук. Зак по рекл', 'type' => 'auc_orders'],
                ['name' => 'Аук. CTR', 'type' => 'auc_ctr'],
                // ['name' => 'Аук. CPC', 'type' => 'auc_cpc'],
                ['name' => 'Заказы по рекл', 'type' => 'ad_orders'],
                ['name' => 'Заказы не по рекл', 'type' => 'no_ad_orders'],
                ['name' => 'Заказы с других РК', 'type' => 'assoc_orders_from_other'],
                ['name' => 'Заказы товаров с РК этого', 'type' => 'assoc_orders_from_this'],
            ]
        ];
    }

    private function accumulateMonthlyTotals(array &$totals, $row, $ordersProfit, /* $buyoutsProfit */): void
    {
        $fields = [
            'orders_count' => $row->orders_count,
            'advertising_costs' => $row->advertising_costs,
            'orders_profit' => $ordersProfit,
            'price_with_disc' => round($row->price_with_disc),
            'finished_price' => $row->finished_price * $row->orders_count,
            'orders_sum_rub' => $row->orders_sum_rub,
            'buyouts_sum_rub' => $row->buyouts_sum_rub,
            'buyouts_count' => $row->buyouts_count,
            // 'buyouts_profit' => $buyoutsProfit,
            'open_card_count' => $row->open_card_count,
            'add_to_cart_count' => $row->add_to_cart_count,
            'aac_sum' => $row->aac_sum,
            'aac_orders' => $row->aac_orders,
            'auc_sum' => $row->auc_sum,
            'auc_orders' => $row->auc_orders,
        ];

        foreach ($fields as $field => $value) {
            if (is_numeric($value)) {
                $totals[$field] = ($totals[$field] ?? 0) + $value;
            }
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

    private function calculateCpc($sum, $clicks): string
    {
        if (!is_numeric($sum) || !is_numeric($clicks) || $clicks <= 0) {
            return '';
        }
        $cpc = $sum / $clicks;
        return round($cpc, 2);
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
            $sum === null || $commission === null ||
            $logistics === null || $advertising_costs === null ||
            $costWithTaxes === null
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
