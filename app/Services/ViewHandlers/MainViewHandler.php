<?php

namespace App\Services\ViewHandlers;

use App\Models\WbAdvV2FullstatsProduct;
use App\Models\WorkSpace;
use Carbon\Carbon;

class MainViewHandler implements ViewHandler
{
    public function prepareData(WorkSpace $workSpace): array
    {
        $shop = $workSpace->shop;
        $commission = $shop->settings['commission'] ?? null;
        $logistics = $shop->settings['logistics'] ?? null;

        $viewSettings = json_decode($workSpace->viewSettings->settings);
        $viewId = $workSpace->viewSettings->view_id;

        $dates = collect(range(0, $viewSettings->days))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->all();

        // Получаем данные по рекламным кампаниям с CTR и CPC
        $advDataByType = $this->getAdvDataWithCtrCpc($shop, $dates);
        $aacData = $advDataByType[8] ?? [];
        $aucData = $advDataByType[9] ?? [];

        $warehouses = [
            'elektrostal' => 'Электросталь',
            'tula' => 'Тула',
            'nevinnomyssk' => 'Невинномысск',
            'krasnodar' => 'Краснодар',
            'kazan' => 'Казань'
        ];

        $salesDataStartDate = Carbon::now()->subDays($viewSettings->days)->format('Y-m-d');
        $totalsStartDate = Carbon::now()->subDays(30)->format('Y-m-d');

        $stocks = $this->calculateStocks($shop, $warehouses);
        $salesByWarehouse = $this->calculateSalesByWarehouse($shop, $totalsStartDate, $warehouses);

        $goods = $workSpace->connectedGoodLists()
            ->with([
                'goods' => function ($query) use ($totalsStartDate, $viewId, $dates) {
                    $query->with([
                        'nsi:good_id,name,variant,fg_1,cost_with_taxes',
                        'sizes:good_id,price',
                        'wbListGoodRow:good_id,discount',
                        'salesFunnel' => function ($q) use ($totalsStartDate) {
                            $q->where('date', '>=', $totalsStartDate)
                                ->orderBy('date');
                        },
                        'notes' => function ($q) use ($viewId, $dates) {
                            $q->select('good_id', 'date')
                                ->where('view_id', $viewId)
                                ->whereIn('date', $dates);
                        },
                        'wbNmReportDetailHistory' => function ($q) use ($dates) {
                            $q->select('good_id', 'dt', 'add_to_cart_conversion', 'cart_to_order_conversion')
                                ->whereIn('dt', $dates);
                        }
                    ]);
                }
            ])
            ->get()
            ->flatMap(function ($list) {
                return $list->goods;
            });

        return $goods->map(function ($good) use (
            $commission,
            $logistics,
            $salesDataStartDate,
            $stocks,
            $salesByWarehouse,
            $dates,
            $aacData,
            $aucData,
        ) {
            $conversionMap = [];
            foreach ($good->wbNmReportDetailHistory as $conversionData) {
                $conversionMap[$conversionData->dt] = [
                    'add_to_cart_conversion' => $conversionData->add_to_cart_conversion ?? 0,
                    'cart_to_order_conversion' => $conversionData->cart_to_order_conversion ?? 0
                ];
            }

            $salesData = [];
            $totals = [
                'orders_count' => 0,
                'orders_sum_rub' => 0,
                'advertising_costs' => 0,
                'price_with_disc' => 0,
                'finished_price' => 0,
                'profit' => 0
            ];

            // Формируем salesData и считаем totals за один проход
            foreach ($good->salesFunnel as $row) {
                $conversion = $conversionMap[$row->date] ?? [
                    'add_to_cart_conversion' => 0,
                    'cart_to_order_conversion' => 0
                ];

                $ordersProfit = $this->calculateProfit($row->orders_sum_rub, $row->orders_count, $row->advertising_costs, $good->nsi, $commission, $logistics);
                $buyoutsProfit = $this->calculateProfit($row->buyouts_sum_rub, $row->buyouts_count, $row->advertising_costs, $good->nsi, $commission, $logistics);

                $noteDates = $good->notes
                    ->map(fn($note) => Carbon::parse($note->date)->format('Y-m-d'))
                    ->toArray();

                $isNotesExists = collect($dates)
                    ->mapWithKeys(fn($date) => [$date => in_array($date, $noteDates)])
                    ->all();

                // Формируем salesData только для дней из viewSettings
                $rowDate = Carbon::parse($row->date);
                if ($rowDate >= Carbon::parse($salesDataStartDate)) {
                    $salesData[$row->date] = [
                        'orders_count' => $row->orders_count === 0 ? '' : $row->orders_count,
                        'advertising_costs' => $row->advertising_costs === 0 ? '' : round($row->advertising_costs / 1000, 1),
                        'price_with_disc' => $row->price_with_disc === 0 ? '' : round($row->price_with_disc),
                        'spp' => $row->price_with_disc != 0 ? round($row->price_with_disc - $row->finished_price) : '',
                        'finished_price' => $row->finished_price == 0 ? '' : round($row->finished_price),
                        'orders_profit' => $ordersProfit,
                        'orders_sum_rub' => $row->orders_sum_rub === 0 ? '' : round($row->orders_sum_rub / 1000),
                        'buyouts_sum_rub' => $row->buyouts_sum_rub === 0 ? '' : round($row->buyouts_sum_rub / 1000),
                        'isHighlighted' => $row->advertising_costs != 0 && $row->advertising_costs > 100,
                        'buyouts_count' => $row->buyouts_count === 0 ? '' : $row->buyouts_count,
                        'buyouts_profit' => $buyoutsProfit,
                        'open_card_count' => $row->open_card_count === 0 ? '' : $row->open_card_count,
                        'no_ad_clicks' => ($row->aac_clicks != 0 || $row->auc_clicks != 0) ? $row->open_card_count - ($row->aac_clicks + $row->auc_clicks) : '',
                        'add_to_cart_count' => $row->add_to_cart_count === 0 ? '' : $row->add_to_cart_count,
                        'add_to_cart_conversion' => $conversion['add_to_cart_conversion'] === 0 ? '' : $conversion['add_to_cart_conversion'],
                        'cart_to_order_conversion' => $conversion['cart_to_order_conversion'] === 0 ? '' : $conversion['cart_to_order_conversion'],

                        'aac_cpm' => $row->aac_cpm === 0 ? '' : $row->aac_cpm,
                        'aac_views' => $row->aac_views === 0 ? '' : $row->aac_views,
                        'aac_clicks' => $row->aac_clicks === 0 ? '' : $row->aac_clicks,
                        'aac_sum' => $row->aac_sum === 0 ? '' : round($row->aac_sum),
                        'aac_orders' => $row->aac_orders === 0 ? '' : $row->aac_orders,
                        'aac_ctr' => $aacData[$row->date][$row->good_id]['ctr'] ?? '',
                        'aac_cpc' => $aacData[$row->date][$row->good_id]['cpc'] ?? '',

                        'auc_cpm' => $row->auc_cpm === 0 ? '' : $row->auc_cpm,
                        'auc_views' => $row->auc_views === 0 ? '' : $row->auc_views,
                        'auc_clicks' => $row->auc_clicks === 0 ? '' : $row->auc_clicks,
                        'auc_sum' => $row->auc_sum === 0 ? '' : round($row->auc_sum),
                        'auc_orders' => $row->auc_orders === 0 ? '' : $row->auc_orders,
                        'auc_ctr' => $aucData[$row->date][$row->good_id]['ctr'] ?? '',
                        'auc_cpc' => $aucData[$row->date][$row->good_id]['cpc'] ?? '',

                        'ad_orders' => ($row->auc_orders != 0 || $row->aac_orders != 0) ? $row->auc_orders + $row->aac_orders : '',
                        'no_ad_orders' => (($row->auc_orders != 0 || $row->aac_orders != 0) && $row->orders_count != 0) ?
                            $row->orders_count - ($row->auc_orders + $row->aac_orders) : '',
                    ];
                }

                // Считаем totals для всех дней за 30 дней
                if (is_numeric($row->orders_count)) $totals['orders_count'] += $row->orders_count;
                if (is_numeric($row->orders_sum_rub)) $totals['orders_sum_rub'] += $row->orders_sum_rub;
                if (is_numeric($row->advertising_costs)) $totals['advertising_costs'] += $row->advertising_costs;
                if (is_numeric($row->finished_price)) $totals['finished_price'] += $row->finished_price * $row->orders_count;
                if (is_numeric($ordersProfit)) $totals['profit'] += $ordersProfit * 1000;
            }

            // Форматируем итоги
            $totals['orders_sum_rub'] = round($totals['orders_sum_rub'] / 1000);
            $totals['advertising_costs'] = round($totals['advertising_costs'] / 1000);
            $totals['profit'] = round($totals['profit'] / 1000);

            // Calculate prices
            $price = $good->sizes->first()?->price ?? 0;
            $discount = $good->wbListGoodRow?->discount ?? 0;
            $discountedPrice = $price * (1 - $discount / 100);

            $mainRowProfit = $this->calculateMainRowProfit(
                $discountedPrice,
                $commission,
                $logistics,
                $good->nsi->cost_with_taxes ?? null
            );
            $percent = ($mainRowProfit == '?' || $discountedPrice == 0) ? '?' : round(($mainRowProfit / $discountedPrice) * 100);
            $ddr = ($totals['advertising_costs'] == 0 || $totals['orders_sum_rub'] == 0) ? 0 :
                $totals['advertising_costs'] / $totals['orders_sum_rub'];

            $daysOfStock = $this->calculateDaysOfStock(
                $salesData,
                $stocks['totals']->get($good->nm_id, 0),
                $shop->settings['percentile_coefficient'] ?? 0.8,
                $shop->settings['weight_coefficient'] ?? 0.7
            );

            return [
                'id' => $good->id,
                'stocks' => [
                    'totals' => $stocks['totals']->get($good->nm_id, 0),
                    'elektrostal' => $stocks['elektrostal']->get($good->nm_id, 0),
                    'kazan' => $stocks['kazan']->get($good->nm_id, 0),
                    'krasnodar' => $stocks['krasnodar']->get($good->nm_id, 0),
                    'nevinnomyssk' => $stocks['nevinnomyssk']->get($good->nm_id, 0),
                    'tula' => $stocks['tula']->get($good->nm_id, 0),
                ],
                'days_of_stock' => $daysOfStock,
                'article' => $good->vendor_code,
                'prices' => [
                    'discountedPrice' => round($discountedPrice, 2),
                    'price' => $price,
                    'discount' => $discount,
                    'costWithTaxes' => ($good->nsi) == null ? null : $good->nsi->cost_with_taxes,
                ],
                'name' => $good->nsi->name ?? '-',
                'variant' => $good->nsi->variant ?? '-',
                'fg1' => $good->nsi->fg_1 ?? '-',
                'wbArticle' => $good->nm_id,
                'mainRowMetadata' => ['name' => 'Шт', 'type' => 'orders_count'],
                'subRowsMetadata' => [
                    ['name' => 'Рекл', 'type' => 'advertising_costs'],
                    ['name' => 'Приб', 'type' => 'orders_profit'],
                    ['name' => 'Цена', 'type' => 'price_with_disc'],
                    ['name' => 'СПП', 'type' => 'spp'],
                    ['name' => 'Цена СПП', 'type' => 'finished_price'],
                    ['name' => 'Заказы руб', 'type' => 'orders_sum_rub'],
                    ['name' => 'Продажи руб', 'type' => 'buyouts_sum_rub'],
                    ['name' => 'Продажи шт', 'type' => 'buyouts_count'],
                    ['name' => 'Приб по прод', 'type' => 'buyouts_profit'],
                    ['name' => 'Клики всего', 'type' => 'open_card_count'],
                    ['name' => 'Клики не рекл', 'type' => 'no_ad_clicks'],
                    ['name' => 'Корзины', 'type' => 'add_to_cart_count'],
                    ['name' => 'Конв корз', 'type' => 'add_to_cart_conversion'],
                    ['name' => 'Конв заказ', 'type' => 'cart_to_order_conversion'],
                    ['name' => 'АРК CPM', 'type' => 'aac_cpm'],
                    ['name' => 'АРК Показы', 'type' => 'aac_views'],
                    ['name' => 'АРК Клики', 'type' => 'aac_clicks'],
                    ['name' => 'АРК Затраты', 'type' => 'aac_sum'],
                    ['name' => 'АРК Зак по рекл', 'type' => 'aac_orders'],
                    ['name' => 'АРК CTR', 'type' => 'aac_ctr'],
                    ['name' => 'АРК CPC', 'type' => 'aac_cpc'],
                    ['name' => 'Аукцион CPM', 'type' => 'auc_cpm'],
                    ['name' => 'Аукцион Показы', 'type' => 'auc_views'],
                    ['name' => 'Аукцион Клики', 'type' => 'auc_clicks'],
                    ['name' => 'Аукцион Затраты', 'type' => 'auc_sum'],
                    ['name' => 'Аукцион Зак по рекл', 'type' => 'auc_orders'],
                    ['name' => 'Аукцион CTR', 'type' => 'auc_ctr'],
                    ['name' => 'Аукцион CPC', 'type' => 'auc_cpc'],
                    ['name' => 'Заказы по рекл', 'type' => 'ad_orders'],
                    ['name' => 'Заказы не по рекл', 'type' => 'no_ad_orders'],
                ],
                'isNotesExists' => $isNotesExists ?? [],
                'totals' => [
                    'orders_count' => $totals['orders_count'] == 0 ? '' : $totals['orders_count'],
                    'orders_sum_rub' => $totals['orders_sum_rub'] == 0 ? '' : $totals['orders_sum_rub'],
                    'advertising_costs' => $totals['advertising_costs'] == 0 ? '' : $totals['advertising_costs'],
                    'finished_price' => ($totals['finished_price'] == 0 || $totals['orders_count'] == 0) ? '' :
                        round($totals['finished_price'] / $totals['orders_count']),
                    'profit' => $totals['profit'] == 0 ? '' : round($totals['profit']),
                    'price_with_disc' => $totals['price_with_disc'] == 0 ? '' : $totals['price_with_disc'],
                ],
                'salesByWarehouse' => [
                    'elektrostal' => $salesByWarehouse['elektrostal']->get($good->nm_id)['orders_count'] ?? 0,
                    'kazan' => $salesByWarehouse['kazan']->get($good->nm_id)['orders_count'] ?? 0,
                    'krasnodar' => $salesByWarehouse['krasnodar']->get($good->nm_id)['orders_count'] ?? 0,
                    'nevinnomyssk' => $salesByWarehouse['nevinnomyssk']->get($good->nm_id)['orders_count'] ?? 0,
                    'tula' => $salesByWarehouse['tula']->get($good->nm_id)['orders_count'] ?? 0,
                ],
                'salesData' => $salesData,
                'mainRowProfit' => $mainRowProfit == '?' ? $mainRowProfit : round($mainRowProfit),
                'percent' => $percent,
                'ddr' => $ddr == 0 ? '' : round($ddr, 2),
            ];
        })->toArray();
    }

    private function calculateDaysOfStock(array $salesData, float $totalStock, float $percentileCoefficient, float $weightCoefficient): string
    {
        try {
            $salesByDay = array_filter(array_column($salesData, 'orders_count'), 'is_numeric');

            if (count($salesByDay) < 3) {
                throw new \Exception('Not enough data');
            }

            $recentSales = array_slice($salesByDay, 0, 3);
            $olderSales = array_slice($salesByDay, 3, 7);

            $percentile = function ($values, $percentile) {
                if (empty($values)) return 0;
                sort($values);
                $index = ($percentile / 100) * (count($values) - 1);
                if (floor($index) == $index) {
                    return $values[$index];
                }
                return $values[floor($index)] +
                    ($values[ceil($index)] - $values[floor($index)]) *
                    ($index - floor($index));
            };

            $recentPercentile = $percentile($recentSales, $percentileCoefficient * 100);
            $olderPercentile = $percentile($olderSales, $percentileCoefficient * 100);

            $dailySalesEstimate = ($recentPercentile * $weightCoefficient) +
                ($olderPercentile * (1 - $weightCoefficient));

            return $dailySalesEstimate > 0 ?
                round($totalStock / $dailySalesEstimate) :
                '?';
        } catch (\Exception $e) {
            return '?';
        }
    }

    private function calculateMainRowProfit(float $discountedPrice, ?float $commission, ?float $logistics, ?float $costWithTaxes): string
    {
        try {
            if ($commission === null || $logistics === null || $costWithTaxes === null) {
                return '?';
            }

            $profit = $discountedPrice - ($discountedPrice * ($commission / 100)) - $logistics - $costWithTaxes;
            return round($profit) == 0 ? '0' : round($profit);
        } catch (\Exception $e) {
            return '?';
        }
    }

    private function calculateProfit($sum, $count, $advertising_costs, $nsi, $commission, $logistics): string
    {
        try {
            $costWithTaxes = $nsi->cost_with_taxes ?? null;

            if (
                $sum === null || $commission === null ||
                $logistics === null || $advertising_costs === null ||
                $costWithTaxes === null
            ) {
                return '?';
            }

            $commissionPercent = $commission / 100;

            $profit = $sum
                - ($sum * $commissionPercent)
                - ($count * $logistics)
                - $advertising_costs
                - ($count * $costWithTaxes);

            // return round($profit, 1) == 0 ? '' : round($profit / 1000);
            return round($profit, 1) == 0 ? '' : round($profit);
        } catch (\Exception $e) {
            return '-';
        }
    }

    public function getDefaultViewState(): array
    {
        return [
            'expandedRows' => [],
            'allExpanded' => false,
            'selectedItems' => [],
            'showOnlySelected' => false,
        ];
    }

    public function getComponent(): string
    {
        return 'MainView';
    }

    private function calculateStocks($shop, $warehouses): array
    {
        // Общие остатки
        $totals = $shop->stocks()
            ->selectRaw('nm_id, sum(quantity) as total')
            ->groupBy('nm_id')
            ->pluck('total', 'nm_id');

        // Остатки по складам
        $warehouseStocks = $shop->stocks()
            ->whereIn('warehouse_name', array_values($warehouses))
            ->selectRaw('nm_id, warehouse_name, sum(quantity) as quantity')
            ->groupBy('nm_id', 'warehouse_name')
            ->get()
            ->mapToGroups(function ($item) {
                return [$item->nm_id => $item];
            });

        // Формируем результат
        $result = ['totals' => $totals];
        foreach ($warehouses as $key => $name) {
            $result[$key] = $warehouseStocks->map(function ($items) use ($name) {
                return $items->firstWhere('warehouse_name', $name)?->quantity ?? 0;
            });
        }

        return $result;
    }

    private function calculateSalesByWarehouse($shop, $startDate, $warehouses): array
    {
        // Продажи по складам (количество записей за последние 30 дней)
        $warehouseSales = $shop->WbV1SupplierOrders()
            ->where('date', '>=', $startDate)
            ->whereIn('warehouse_name', array_values($warehouses))
            ->selectRaw('nm_id, warehouse_name, count(*) as order_count')
            ->groupBy('nm_id', 'warehouse_name')
            ->get()
            ->groupBy('nm_id');

        // Формируем результат
        foreach ($warehouses as $key => $name) {
            $result[$key] = collect();
            if ($warehouseSales->isNotEmpty()) {
                $result[$key] = $warehouseSales->map(function ($items) use ($name) {
                    $item = $items->firstWhere('warehouse_name', $name);
                    return $item ? [
                        'orders_count' => $item->order_count
                    ] : [
                        'orders_count' => 0
                    ];
                });
            }
        }

        return $result;
    }

    private function getAdvDataWithCtrCpc($shop, $dates)
    {
        return WbAdvV2FullstatsProduct::whereIn('wb_adv_fs_products.date', $dates)
            ->join('wb_adv_fs_apps', 'wb_adv_fs_products.wb_adv_fs_app_id', '=', 'wb_adv_fs_apps.id')
            ->join('wb_adv_fs_days', 'wb_adv_fs_apps.wb_adv_fs_day_id', '=', 'wb_adv_fs_days.id')
            ->join('wb_adv_v2_fullstats_wb_adverts', 'wb_adv_fs_days.wb_adv_v2_fullstats_wb_advert_id', '=', 'wb_adv_v2_fullstats_wb_adverts.id')
            ->join('wb_adv_v1_promotion_counts', 'wb_adv_v2_fullstats_wb_adverts.advert_id', '=', 'wb_adv_v1_promotion_counts.advert_id')
            ->where('wb_adv_v1_promotion_counts.shop_id', $shop->id)
            ->whereIn('wb_adv_v1_promotion_counts.type', [8, 9])
            ->select(
                'wb_adv_fs_products.good_id',
                'wb_adv_fs_products.date',
                'wb_adv_v1_promotion_counts.type',
                'wb_adv_v2_fullstats_wb_adverts.ctr',
                'wb_adv_v2_fullstats_wb_adverts.cpc'
            )
            ->get()
            ->groupBy(['type', 'date', 'good_id'])
            ->map(function ($typeGroup) {
                return $typeGroup->map(function ($dateGroup) {
                    return $dateGroup->map(function ($items) {
                        return [
                            'ctr' => $items->first()->ctr,
                            'cpc' => $items->first()->cpc,
                        ];
                    });
                });
            });
    }
}
