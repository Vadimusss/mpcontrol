<?php

namespace App\Services\ViewHandlers;

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
        $dates = collect(range(0, $viewSettings->days))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->all();

        $totalsStartDate = Carbon::now()->subDays(30)->format('Y-m-d');

        $currentDate = Carbon::now()->subDays(1)->format('Y-m-d');

        $warehouses = [
            'elektrostal' => 'Электросталь',
            'tula' => 'Тула',
            'nevinnomyssk' => 'Невинномысск',
            'krasnodar' => 'Краснодар',
            'kazan' => 'Казань'
        ];

        $stocks = $this->calculateStocks($shop, $warehouses, $currentDate);

        $goodsWithTotals = $workSpace->connectedGoodLists()
            ->with([
                'goods' => function ($query) use ($totalsStartDate) {
                    $query->with([
                        'salesFunnel' => function ($q) use ($totalsStartDate) {
                            $q->where('date', '>=', $totalsStartDate)
                                ->select('good_id', 'orders_count');
                        }
                    ]);
                }
            ])
            ->get()
            ->flatMap(function ($list) {
                return $list->goods;
            });

        // Создаем маппинг totalsOrdersCount по good_id
        $totalsOrdersCountMap = [];
        foreach ($goodsWithTotals as $good) {
            $total = 0;
            foreach ($good->salesFunnel as $row) {
                if (is_numeric($row->orders_count)) {
                    $total += $row->orders_count;
                }
            }
            $totalsOrdersCountMap[$good->id] = $total;
        }

        $goods = $workSpace->connectedGoodLists()
            ->with([
                'goods' => function ($query) use ($dates) {
                    $query->with([
                        'nsi:good_id,name,variant,cost_with_taxes',
                        'sizes:good_id,price',
                        'wbListGoodRow:good_id,discount',
                        'salesFunnel' => function ($q) use ($dates) {
                            $q->whereIn('date', $dates)
                                ->select('good_id', 'date', 'orders_count')
                                ->orderBy('date');
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
            $stocks,
            $dates,
            $totalsOrdersCountMap,
        ) {

            $ordersCountByDate = [];

            foreach ($good->salesFunnel as $row) {
                if (is_numeric($row->orders_count)) {
                    $ordersCountByDate[$row->date] = $row->orders_count === 0 ? '' : $row->orders_count;
                }
            }

            foreach ($dates as $date) {
                if (!isset($ordersCountByDate[$date])) {
                    $ordersCountByDate[$date] = '';
                }
            }

            $price = $good->sizes->first()?->price ?? 0;
            $discount = $good->wbListGoodRow?->discount ?? 0;
            $discountedPrice = $price * (1 - $discount / 100);

            $costWithTaxes = $good->nsi?->cost_with_taxes;

            $mainRowProfit = $this->calculateMainRowProfit(
                $price,
                $commission,
                $logistics,
                $costWithTaxes
            );
            $percent = ($mainRowProfit == '' || $discountedPrice == 0) ? '' : round(($mainRowProfit / $discountedPrice) * 100);

            return [
                'id' => $good->id,
                'stocks' => [
                    'totals' => $stocks['fboTotals']->get($good->nm_id, 0) + $stocks['fbsTotals']->get($good->nm_id, 0),
                    'fboTotals' => $stocks['fboTotals']->get($good->nm_id, 0),
                    'fbsTotals' => $stocks['fbsTotals']->get($good->nm_id, 0),
                    'elektrostal' => $stocks['elektrostal']->get($good->nm_id, 0),
                    'kazan' => $stocks['kazan']->get($good->nm_id, 0),
                    'krasnodar' => $stocks['krasnodar']->get($good->nm_id, 0),
                    'nevinnomyssk' => $stocks['nevinnomyssk']->get($good->nm_id, 0),
                    'tula' => $stocks['tula']->get($good->nm_id, 0),
                ],
                'days_of_stock' => $this->calculateDaysOfStock($ordersCountByDate, $stocks['fboTotals']->get($good->nm_id, 0)),
                'article' => $good->vendor_code,
                'prices' => [
                    'discountedPrice' => round($discountedPrice),
                    'price' => $price,
                    'discount' => $discount,
                    'costWithTaxes' => $costWithTaxes ? round($costWithTaxes) : null,
                ],
                'name' => $good->nsi->name ?? '-',
                'variant' => $good->nsi->variant ?? '-',
                'wbArticle' => $good->nm_id,
                'mainRowMetadata' => 'Шт.',
                'totalsOrdersCount' => $totalsOrdersCountMap[$good->id] ?? 0,
                'orders_count' => $ordersCountByDate,
                'mainRowProfit' => $mainRowProfit == '' ? $mainRowProfit : round($mainRowProfit),
                'percent' => $percent,
            ];
        })->toArray();
    }

    private function calculateDaysOfStock(array $ordersCountByDate, float $totalStock): string
    {
        $salesByDay = array_filter($ordersCountByDate, 'is_numeric');

        if (count($salesByDay) < 3) {
            return '';
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

        $recentPercentile = $percentile($recentSales, 80);
        $olderPercentile = $percentile($olderSales, 80);

        $dailySalesEstimate = ($recentPercentile * 0.7) + ($olderPercentile * 0.3);

        return $dailySalesEstimate > 0 ? round($totalStock / $dailySalesEstimate) : '';
    }

    private function calculateMainRowProfit(float $price, ?float $commission, ?float $logistics, ?float $costWithTaxes): string
    {
        if ($commission === null || $logistics === null || $costWithTaxes === null) {
            return '';
        }

        $profit = $price - ($price * ($commission / 100)) - $logistics - $costWithTaxes;
        return round($profit) == 0 ? '' : round($profit);
    }

    public function getDefaultViewState(): array
    {
        return [
            'expandedRows' => [],
            'selectedItems' => [],
            'showOnlySelected' => false,
        ];
    }

    public function getComponent(): string
    {
        return 'VirtualizedMainView';
    }

    private function calculateStocks($shop, $warehouses, $date): array
    {
        $fboTotals = $shop->stocks()
            ->selectRaw('nm_id, sum(quantity) as total')
            ->groupBy('nm_id')
            ->pluck('total', 'nm_id');

        $fbsTotals = $shop->fbsStocks()
            ->where('date', '=', $date)
            ->selectRaw('nm_id, sum(amount) as total')
            ->groupBy('nm_id')
            ->pluck('total', 'nm_id');

        $warehouseStocks = $shop->stocks()
            ->whereIn('warehouse_name', array_values($warehouses))
            ->selectRaw('nm_id, warehouse_name, sum(quantity) as quantity')
            ->groupBy('nm_id', 'warehouse_name')
            ->get()
            ->mapToGroups(function ($item) {
                return [$item->nm_id => $item];
            });

        $result['fboTotals'] = $fboTotals;
        $result['fbsTotals'] = $fbsTotals;
        foreach ($warehouses as $key => $name) {
            $result[$key] = $warehouseStocks->map(function ($items) use ($name) {
                return $items->firstWhere('warehouse_name', $name)?->quantity ?? 0;
            });
        }

        return $result;
    }
}
