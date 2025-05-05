<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Models\Good;
use App\Models\Shop;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Throwable;

class GenerateSalesFunnelReport implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public string $day,
        public $timeout = 1200,
    ) {
        $this->day = $day;
        $this->shop = $shop;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $this->shop->salesFunnel()->where('date', '=', $this->day)->delete();

        $WbNmReportDetailHistory = $this->shop->WbNmReportDetailHistory()->select(
            'good_id',
            'wb_nm_report_detail_histories.vendor_code',
            'wb_nm_report_detail_histories.nm_id',
            'imt_name',
            'dt',
            'open_card_count',
            'add_to_cart_count',
            'orders_count',
            'orders_sum_rub'
        )->where('dt', '=', $this->day)->get();

        $advCostsSumByGoodId = $this->shop->wbAdvV2FullstatsWbAdverts()
            ->with(['wbAdvV2FullstatsDays.wbAdvV2FullstatsApps.wbAdvV2FullstatsProducts' => function ($query) {
                $query->where('date', $this->day)
                    ->select('wb_adv_fs_app_id', 'good_id', 'sum');
            }])->get()
            ->pluck('wbAdvV2FullstatsDays')->collapse()
            ->pluck('wbAdvV2FullstatsApps')->collapse()
            ->pluck('wbAdvV2FullstatsProducts')->collapse()->groupBy('good_id')
            ->map(function ($products) {
                return round($products->sum('sum'));
            })
            ->toArray();

        $advDataByType = $this->shop->wbAdvV1PromotionCounts()
            ->whereIn('type', [8, 9])
            ->with(['wbAdvV2FullstatsWbAdverts.wbAdvV2FullstatsDays.wbAdvV2FullstatsApps.wbAdvV2FullstatsProducts' => function ($query) {
                $query->where('date', $this->day)
                    ->select('wb_adv_fs_app_id', 'good_id', 'sum', 'views', 'clicks', 'orders');
            }])->get()
            ->groupBy('type')
            ->map(function ($typeGroup, $type) {
                return $typeGroup->flatMap(function ($item) {
                    return $item->wbAdvV2FullstatsWbAdverts;
                })
                    ->flatMap(function ($advert) {
                        return $advert->wbAdvV2FullstatsDays;
                    })
                    ->flatMap(function ($day) {
                        return $day->wbAdvV2FullstatsApps;
                    })
                    ->flatMap(function ($app) {
                        return $app->wbAdvV2FullstatsProducts;
                    })
                    ->groupBy('good_id')
                    ->map(function ($products) use ($type) {
                        $sum = $products->sum('sum');
                        $views = $products->sum('views');
                        return [
                            'sum' => round($sum),
                            'views' => $views,
                            'clicks' => $products->sum('clicks'),
                            'orders' => $products->sum('orders'),
                            'cpm' => $views > 0 ? round(($sum / $views) * 1000, 2) : 0
                        ];
                    });
            });

        $aacData = $advDataByType->get(8, collect())->toArray();
        $aucData = $advDataByType->get(9, collect())->toArray();

        $WbV1SupplierOrders = $this->shop->WbV1SupplierOrders()->select('nm_id', 'finished_price', 'price_with_disc')->where('date', 'like', "%{$this->day}%")->get();

        $avgPricesByDay = $WbV1SupplierOrders->groupBy('nm_id')->reduce(function ($carry, $day, $nmId) {
            $carry[$nmId]['finished_price'] = round($day->avg('finished_price'), 2);
            $carry[$nmId]['price_with_disc'] = round($day->avg('price_with_disc'), 2);
            return $carry;
        }, []);

        $report = $WbNmReportDetailHistory->map(function ($row) use ($advCostsSumByGoodId, $aacData, $aucData, $avgPricesByDay) {
            $row->advertising_costs = array_key_exists($row->good_id, $advCostsSumByGoodId) ? $advCostsSumByGoodId[$row->good_id] : 0;
            $row->finished_price = array_key_exists($row->nm_id, $avgPricesByDay) ? $avgPricesByDay[$row->nm_id]['finished_price'] : 0;
            $row->price_with_disc = array_key_exists($row->nm_id, $avgPricesByDay) ? $avgPricesByDay[$row->nm_id]['price_with_disc'] : 0;

            $row->aac_cpm = array_key_exists($row->good_id, $aacData) ? $aacData[$row->good_id]['cpm'] : 0;
            $row->aac_views = array_key_exists($row->good_id, $aacData) ? $aacData[$row->good_id]['views'] : 0;
            $row->aac_clicks = array_key_exists($row->good_id, $aacData) ? $aacData[$row->good_id]['clicks'] : 0;
            $row->aac_orders = array_key_exists($row->good_id, $aacData) ? $aacData[$row->good_id]['orders'] : 0;
            $row->aac_sum = array_key_exists($row->good_id, $aacData) ? $aacData[$row->good_id]['sum'] : 0;

            $row->auc_cpm = array_key_exists($row->good_id, $aucData) ? $aucData[$row->good_id]['cpm'] : 0;
            $row->auc_views = array_key_exists($row->good_id, $aucData) ? $aucData[$row->good_id]['views'] : 0;
            $row->auc_clicks = array_key_exists($row->good_id, $aucData) ? $aucData[$row->good_id]['clicks'] : 0;
            $row->auc_orders = array_key_exists($row->good_id, $aucData) ? $aucData[$row->good_id]['orders'] : 0;
            $row->auc_sum = array_key_exists($row->good_id, $aucData) ? $aucData[$row->good_id]['sum'] : 0;

            return $row;
        });

        $report->each(function ($row) {
            Good::firstWhere('id', $row->good_id)->salesFunnel()->create([
                'vendor_code' => $row->vendor_code,
                'nm_id' => $row->nm_id,
                'imt_name' => $row->imt_name,
                'date' => $row->dt,
                'open_card_count' => $row->open_card_count,
                'add_to_cart_count' => $row->add_to_cart_count,
                'orders_count' => $row->orders_count,
                'orders_sum_rub' => $row->orders_sum_rub,
                'advertising_costs' => $row->advertising_costs,
                'price_with_disc' => $row->price_with_disc,
                'finished_price' => $row->finished_price,
                'aac_cpm' => $row->aac_cpm,
                'aac_views' => $row->aac_views,
                'aac_clicks' => $row->aac_clicks,
                'aac_orders' => $row->aac_orders,
                'aac_sum' => $row->aac_sum,
                'auc_cpm' => $row->auc_cpm,
                'auc_views' => $row->auc_views,
                'auc_clicks' => $row->auc_clicks,
                'auc_orders' => $row->auc_orders,
                'auc_sum' => $row->auc_sum,
            ]);
        });

        $message = $message = "Воронка продаж магазина {$this->shop->name} за {$this->day} обновлена!";
        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('GenerateSalesFunnelReport', $duration, $message);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('GenerateSalesFunnelReport', $exception);
    }
}
