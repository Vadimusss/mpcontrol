<?php

namespace App\Services\ViewHandlers;

use App\Models\WorkSpace;
use App\Services\MainViewCacheService;
use Carbon\Carbon;

class MainViewHandler implements ViewHandler
{
    public function prepareData(WorkSpace $workSpace): array
    {
        $cacheService = app(MainViewCacheService::class);
        $cachedData = $cacheService->getForWorkspace($workSpace);

        if ($cachedData !== null) {
            return $cachedData;
        }

        return $this->prepareDataWithoutCache($workSpace);
    }

    public function prepareDataWithoutCache(WorkSpace $workSpace): array
    {
        $shop = $workSpace->shop;
        $commission = $shop->settings['commission'] ?? null;
        $logistics = $shop->settings['logistics'] ?? null;

        // $viewSettings = json_decode($workSpace->viewSettings->settings);
        $dates = collect(range(0, 30))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->all();

        $totalsStartDate = Carbon::now()->subDays(30)->format('Y-m-d');

        $currentDate = Carbon::now()->subDays(1)->format('Y-m-d');

        $warehouses = [
            'elektrostal' => 'Электросталь',
            'tula' => 'Тула',
            'koledino' => 'Коледино',
            'ryazan' => 'Рязань (Тюшевское)',
            'nevinnomyssk' => 'Невинномысск',
            'krasnodar' => 'Краснодар',
            'kazan' => 'Казань',
            'kotovsk' => 'Котовск',
            'belyeStolby' => 'Белые Столбы',
            'podolsk4' => 'Подольск 4',
            'spbUtkinaZavod' => 'Санкт-Петербург Уткина Заводь',
            'podolsk' => 'Подольск',
            'ekbIspytatelej14g' => 'Екатеринбург - Испытателей 14г',
            'novosibirsk' => 'Новосибирск',
            'voronezh' => 'Воронеж',
            'vladimir' => 'Владимир',
            'belayaDacha' => 'Белая дача',
            'samara' => 'Самара (Новосемейкино)',
            'volgograd' => 'Волгоград',
            'ekbPerspektivnyj12' => 'Екатеринбург - Перспективный 12',
            'sarapul' => 'Сарапул',
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
                        'internalNsi:good_id,product_name,cost_price',
                        'sizes:good_id,price',
                        'status',
                        'wbListGoodRow:good_id,discount',
                        'salesFunnel' => function ($q) use ($dates) {
                            $q->whereIn('date', $dates)
                                ->select('good_id', 'date', 'orders_count', 'advertising_costs')
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
            $isHighlightedByDate = [];

            foreach ($good->salesFunnel as $row) {
                if (is_numeric($row->orders_count)) {
                    $ordersCountByDate[$row->date] = $row->orders_count === 0 ? 0 : $row->orders_count;
                }
                if (is_numeric($row->advertising_costs)) {
                    $isHighlightedByDate[$row->date] = $row->advertising_costs < 100 ? false : true;
                }
            }

            foreach ($dates as $date) {
                if (!isset($ordersCountByDate[$date])) {
                    $ordersCountByDate[$date] = 0;
                }
                if (!isset($isHighlightedByDate[$date])) {
                    $isHighlightedByDate[$date] = false;
                }
            }

            $price = $good->sizes->first()?->price ?? 0;
            $discount = $good->wbListGoodRow?->discount ?? 0;
            $discountedPrice = $price * (1 - $discount / 100);

            // $costWithTaxes = $good->nsi?->cost_with_taxes;

            $costWithTaxes = $good->internalNsi?->cost_price ?? $good->nsi?->cost_with_taxes;


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
                    'tula' => $stocks['tula']->get($good->nm_id, 0),
                    'koledino' => $stocks['koledino']->get($good->nm_id, 0),
                    'ryazan' => $stocks['ryazan']->get($good->nm_id, 0),
                    'nevinnomyssk' => $stocks['nevinnomyssk']->get($good->nm_id, 0),
                    'krasnodar' => $stocks['krasnodar']->get($good->nm_id, 0),
                    'kazan' => $stocks['kazan']->get($good->nm_id, 0),
                    'kotovsk' => $stocks['kotovsk']->get($good->nm_id, 0),
                    'belyeStolby' => $stocks['belyeStolby']->get($good->nm_id, 0),
                    'podolsk4' => $stocks['podolsk4']->get($good->nm_id, 0),
                    'spbUtkinaZavod' => $stocks['spbUtkinaZavod']->get($good->nm_id, 0),
                    'podolsk' => $stocks['podolsk']->get($good->nm_id, 0),
                    'ekbIspytatelej14g' => $stocks['ekbIspytatelej14g']->get($good->nm_id, 0),
                    'novosibirsk' => $stocks['novosibirsk']->get($good->nm_id, 0),
                    'voronezh' => $stocks['voronezh']->get($good->nm_id, 0),
                    'vladimir' => $stocks['vladimir']->get($good->nm_id, 0),
                    'belayaDacha' => $stocks['belayaDacha']->get($good->nm_id, 0),
                    'samara' => $stocks['samara']->get($good->nm_id, 0),
                    'volgograd' => $stocks['volgograd']->get($good->nm_id, 0),
                    'ekbPerspektivnyj12' => $stocks['ekbPerspektivnyj12']->get($good->nm_id, 0),
                    'sarapul' => $stocks['sarapul']->get($good->nm_id, 0),
                ],
                'days_of_stock' => $this->calculateDaysOfStock($ordersCountByDate, $stocks['fboTotals']->get($good->nm_id, 0)),
                'article' => $good->vendor_code,
                'prices' => [
                    'discountedPrice' => round($discountedPrice),
                    'price' => $price,
                    'discount' => $discount,
                    'costWithTaxes' => $costWithTaxes ? round($costWithTaxes) : null,
                ],
                'name' => $good->internalNsi->product_name ?? $good->nsi->name ?? '-',
                'variant' => $good->nsi->variant ?? '-',
                'wbArticle' => $good->nm_id,
                'status' => $good->status->name ?? 'Без статуса',
                'mainRowMetadata' => 'Шт.',
                'totalsOrdersCount' => $totalsOrdersCountMap[$good->id] ?? 0,
                'orders_count' => $ordersCountByDate,
                'isHighlighted' => $isHighlightedByDate,
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
        return 'MainView';
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
