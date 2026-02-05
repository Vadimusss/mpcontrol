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
use App\Models\WbV1SupplierStocks;
use App\Models\SupplierWarehousesStocks;
use Carbon\Carbon;
use Throwable;

class GenerateMainViewCache implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public $timeout = 60,
        public $tries = 1,
    ) {
        $this->shop = $shop;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $data = $this->calculateDataForAllGoods();

        $cacheKey = "main_view_cache:shop_{$this->shop->id}";
        Cache::put($cacheKey, $data, 86400);

        $duration = microtime(true) - $startTime;
        Log::info("MainView cache generated for shop {$this->shop->id} in {$duration}s");
    }

    private function calculateDataForAllGoods(): array
    {
        $shop = $this->shop;
        $commission = $shop->settings['commission'] ?? null;
        $logistics = $shop->settings['logistics'] ?? null;

        $dates = collect(range(0, 29))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->all();

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

        $stocks = $this->calculateStocks($warehouses, $currentDate);

        $goods = Good::where('shop_id', $shop->id)
            ->with([
                'nsi:good_id,name,variant,cost_with_taxes',
                'sizes:good_id,price',
                'wbListGoodRow:good_id,discount',
                'salesFunnel' => function ($q) use ($dates) {
                    $q->whereIn('date', $dates)
                        ->select('good_id', 'date', 'orders_count')
                        ->orderBy('date');
                }
            ])
            ->get(['id', 'nm_id', 'vendor_code']);

        $totalsStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $totalsOrdersCountMap = $this->calculateTotalsOrdersCount($totalsStartDate);

        $result = $goods->map(function ($good) use (
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

            krsort($ordersCountByDate);

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

        return [
            'goods' => $result,
            'calculated_at' => now()->toDateTimeString(),
            'shop_settings' => [
                'commission' => $commission,
                'logistics' => $logistics,
            ]
        ];
    }

    private function calculateTotalsOrdersCount(string $startDate): array
    {
        return SalesFunnel::where('good_id', 'in', function ($query) {
                $query->select('id')
                    ->from('goods')
                    ->where('shop_id', $this->shop->id);
            })
            ->where('date', '>=', $startDate)
            ->select('good_id', DB::raw('SUM(orders_count) as total'))
            ->groupBy('good_id')
            ->pluck('total', 'good_id')
            ->toArray();
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

    private function calculateStocks(array $warehouses, string $date): array
    {
        $fboTotals = WbV1SupplierStocks::where('shop_id', $this->shop->id)
            ->selectRaw('nm_id, sum(quantity) as total')
            ->groupBy('nm_id')
            ->pluck('total', 'nm_id');

        $fbsTotals = SupplierWarehousesStocks::where('shop_id', $this->shop->id)
            ->where('date', '=', $date)
            ->selectRaw('nm_id, sum(amount) as total')
            ->groupBy('nm_id')
            ->pluck('total', 'nm_id');

        $warehouseStocks = WbV1SupplierStocks::where('shop_id', $this->shop->id)
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

    public function failed(?Throwable $exception): void
    {
        Log::error("GenerateMainViewCacheJob failed for shop {$this->shop->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}