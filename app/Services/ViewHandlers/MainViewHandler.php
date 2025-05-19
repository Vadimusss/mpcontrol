<?php

namespace App\Services\ViewHandlers;

use App\Models\WorkSpace;
use App\Models\Good;
use Carbon\Carbon;

class MainViewHandler implements ViewHandler
{
    public function prepareData(WorkSpace $workSpace): array
    {
        $shop = $workSpace->shop;
        $commission = $shop->settings['commission'] ?? null;
        $logistics = $shop->settings['logistics'] ?? null;

        $viewSettings = json_decode($workSpace->viewSettings->settings);

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
                'goods' => function ($query) use ($totalsStartDate) {
                    $query->with([
                        'WbNmReportDetailHistory:good_id,imt_name',
                        'nsi:good_id,name,variant,fg_1,cost_with_taxes',
                        'sizes:good_id,price',
                        'wbListGoodRow:good_id,discount',
                        'salesFunnel' => function ($q) use ($totalsStartDate) {
                            $q->where('date', '>=', $totalsStartDate)
                                ->orderBy('date');
                        }
                    ]);
                }
            ])
            ->get()
            ->flatMap(function ($list) {
                return $list->goods;
            });

        return $goods->map(function ($good) use ($commission, $logistics, $salesDataStartDate, $stocks, $salesByWarehouse) {
            $salesData = [];
            $totals = [
                'orders_count' => 0,
                'orders_sum_rub' => 0,
                'advertising_costs' => 0,
                'finished_price' => 0,
                'profit' => 0
            ];

            // Формируем salesData и считаем totals за один проход
            foreach ($good->salesFunnel as $row) {
                $profit = $this->calculateProfit($row, $good->nsi, $commission, $logistics);

                // Формируем salesData только для дней из viewSettings
                $rowDate = Carbon::parse($row->date);
                if ($rowDate >= Carbon::parse($salesDataStartDate)) {
                    $salesData[$row->date] = [
                        'orders_count' => $row->orders_count === 0 ? '' : $row->orders_count,
                        'orders_sum_rub' => $row->orders_sum_rub === 0 ? '' : round($row->orders_sum_rub / 1000),
                        'advertising_costs' => $row->advertising_costs === 0 ? '' : round($row->advertising_costs / 1000),
                        'finished_price' => $row->finished_price == 0 ? '' : round($row->finished_price),
                        'profit' => $profit,
                    ];
                }

                // Считаем totals для всех дней за 30 дней
                if (is_numeric($row->orders_count)) $totals['orders_count'] += $row->orders_count;
                if (is_numeric($row->orders_sum_rub)) $totals['orders_sum_rub'] += $row->orders_sum_rub;
                if (is_numeric($row->advertising_costs)) $totals['advertising_costs'] += $row->advertising_costs;
                if (is_numeric($row->finished_price)) $totals['finished_price'] += $row->finished_price * $row->orders_count;
                if (is_numeric($profit)) $totals['profit'] += $profit * 1000;
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
                'mainRowMetadata' => ['name' => 'Продажи шт', 'type' => 'orders_count'],
                'subRowsMetadata' => [
                    ['name' => 'Продажи руб', 'type' => 'orders_sum_rub'],
                    ['name' => 'Реклама', 'type' => 'advertising_costs'],
                    ['name' => 'Цена СПП', 'type' => 'finished_price'],
                    ['name' => 'Прибыль', 'type' => 'profit'],
                    ['name' => 'Заметка', 'type' => ''],
                ],
                'totals' => [
                    'orders_count' => $totals['orders_count'] == 0 ? '' : $totals['orders_count'],
                    'orders_sum_rub' => $totals['orders_sum_rub'] == 0 ? '' : $totals['orders_sum_rub'],
                    'advertising_costs' => $totals['advertising_costs'] == 0 ? '' : $totals['advertising_costs'],
                    'finished_price' => ($totals['finished_price'] == 0 || $totals['orders_count'] == 0) ? '' :
                        round($totals['finished_price'] / $totals['orders_count']),
                    'profit' => $totals['profit'] == 0 ? '' : round($totals['profit']),
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

    private function calculateProfit($row, $nsi, $commission, $logistics): string
    {
        try {
            $costWithTaxes = $nsi->cost_with_taxes ?? null;

            if (
                $row->orders_sum_rub === null || $commission === null ||
                $logistics === null || $row->advertising_costs === null ||
                $costWithTaxes === null
            ) {
                return '?';
            }

            $commissionPercent = $commission / 100;

            $profit = $row->orders_sum_rub
                - ($row->orders_sum_rub * $commissionPercent)
                - ($row->orders_count * $logistics)
                - $row->advertising_costs
                - ($row->orders_count * $costWithTaxes);

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
}
