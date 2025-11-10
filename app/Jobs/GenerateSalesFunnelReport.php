<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\DB;
use App\Models\Good;
use App\Models\Shop;
use App\Models\SalesFunnel;
use App\Models\WbRealizationReport;
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
            'orders_sum_rub',
            'buyouts_count',
            'buyouts_sum_rub',
            'buyout_percent'
        )->where('dt', '=', $this->day)->get();

        $advCostsSumByGoodId = DB::table('wb_adv_v3_fs_products as p')
            ->join('wb_adv_v3_fs_apps as a', 'p.wb_adv_v3_fs_app_id', '=', 'a.id')
            ->join('wb_adv_v3_fs_days as d', 'a.wb_adv_v3_fs_day_id', '=', 'd.id')
            ->join('wb_adv_v3_fullstats_wb_adverts as adv', 'd.wb_adv_v3_fullstats_wb_advert_id', '=', 'adv.id')
            ->where('adv.shop_id', $this->shop->id)
            ->where('p.date', $this->day)
            ->whereNotNull('p.good_id')
            ->select('p.good_id', DB::raw('ROUND(SUM(p.sum)) as total_sum'))
            ->groupBy('p.good_id')
            ->pluck('total_sum', 'good_id')
            ->toArray();

        $aacData = $this->getAacData();
        $aucData = $this->getAucData();
        $allData = $this->getAllAdvData();

        $nmIds = $WbNmReportDetailHistory->pluck('nm_id')->toArray();
        $expenseData = WbRealizationReport::getExpenseData($this->day, $nmIds);

        $avgPricesByDay = DB::table('wb_v1_supplier_orders')
            ->where('shop_id', $this->shop->id)
            ->where('date', 'like', "%{$this->day}%")
            ->select(
                'nm_id',
                DB::raw('ROUND(AVG(finished_price), 2) as finished_price'),
                DB::raw('ROUND(AVG(price_with_disc), 2) as price_with_disc')
            )
            ->groupBy('nm_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->nm_id => [
                        'finished_price' => (float)$item->finished_price,
                        'price_with_disc' => (float)$item->price_with_disc
                    ]
                ];
            })
            ->toArray();

        $report = $WbNmReportDetailHistory->map(function ($row) use ($advCostsSumByGoodId, $aacData, $aucData, $allData, $avgPricesByDay, $expenseData) {
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

            $allOrders = array_key_exists($row->good_id, $allData) ? $allData[$row->good_id]['orders'] : 0;
            $aacOrders = array_key_exists($row->good_id, $aacData) ? $aacData[$row->good_id]['orders'] : 0;
            $aucOrders = array_key_exists($row->good_id, $aucData) ? $aucData[$row->good_id]['orders'] : 0;
            $row->assoc_orders = $allOrders - ($aacOrders + $aucOrders);

            $expenseInfo = array_key_exists($row->nm_id, $expenseData) ? $expenseData[$row->nm_id] : null;
            $row->commission_total = $expenseInfo ? $expenseInfo['commission_total'] : 0;
            $row->logistics_total = $expenseInfo ? $expenseInfo['logistics_total'] : 0;
            $row->storage_total = $expenseInfo ? $expenseInfo['storage_total'] : 0;
            $row->acquiring_total = $expenseInfo ? $expenseInfo['acquiring_total'] : 0;
            $row->other_total = $expenseInfo ? $expenseInfo['other_total'] : 0;

            $op_after_spp = $expenseInfo ? $expenseInfo['op_after_spp'] : 0;
            
            $row->profit_without_ads = $op_after_spp - $row->commission_total;
            
            $row->profit_with_ads = $row->profit_without_ads - $row->advertising_costs;

            return $row;
        });

        $data = $report->map(function ($row) {
            return [
                'good_id' => $row->good_id,
                'vendor_code' => $row->vendor_code,
                'nm_id' => $row->nm_id,
                'imt_name' => $row->imt_name,
                'date' => $row->dt,
                'open_card_count' => $row->open_card_count,
                'add_to_cart_count' => $row->add_to_cart_count,
                'orders_count' => $row->orders_count,
                'orders_sum_rub' => $row->orders_sum_rub,
                'buyouts_count' => $row->buyouts_count,
                'buyouts_sum_rub' => $row->buyouts_sum_rub,
                'buyout_percent' => $row->buyout_percent,
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
                'assoc_orders' => $row->assoc_orders,
                'commission_total' => $row->commission_total,
                'logistics_total' => $row->logistics_total,
                'storage_total' => $row->storage_total,
                'acquiring_total' => $row->acquiring_total,
                'other_total' => $row->other_total,
                'profit_without_ads' => $row->profit_without_ads,
                'profit_with_ads' => $row->profit_with_ads,
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray();

        SalesFunnel::insert($data);

        $message = $message = "Воронка продаж магазина {$this->shop->name} за {$this->day} обновлена!";
        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('GenerateSalesFunnelReport', $duration, $message);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('GenerateSalesFunnelReport', $exception);
    }

    private function getAacData(): array
    {
        $type8Query = DB::table('wb_adv_v3_fs_products as p')
            ->join('wb_adv_v3_fs_apps as a', 'p.wb_adv_v3_fs_app_id', '=', 'a.id')
            ->join('wb_adv_v3_fs_days as d', 'a.wb_adv_v3_fs_day_id', '=', 'd.id')
            ->join('wb_adv_v3_fullstats_wb_adverts as adv', 'd.wb_adv_v3_fullstats_wb_advert_id', '=', 'adv.id')
            ->join('wb_adv_v1_promotion_counts as pc', 'adv.advert_id', '=', 'pc.advert_id')
            ->where('adv.shop_id', $this->shop->id)
            ->where('p.date', $this->day)
            ->whereNotNull('p.good_id')
            ->where('pc.type', 8)
            ->join('wb_adv_v1_promotion_adverts as pa', 'adv.advert_id', '=', 'pa.advert_id')
            ->join('wb_adv_v1_promo_nms as pn', 'pa.id', '=', 'pn.wb_adv_v1_promotion_adverts_id')
            ->where('pn.nm', '=', DB::raw('p.nm_id'))
            ->select(
                'p.good_id',
                'p.sum',
                'p.views',
                'p.clicks',
                'p.orders'
            );

        $type9UnifiedQuery = DB::table('wb_adv_v3_fs_products as p')
            ->join('wb_adv_v3_fs_apps as a', 'p.wb_adv_v3_fs_app_id', '=', 'a.id')
            ->join('wb_adv_v3_fs_days as d', 'a.wb_adv_v3_fs_day_id', '=', 'd.id')
            ->join('wb_adv_v3_fullstats_wb_adverts as adv', 'd.wb_adv_v3_fullstats_wb_advert_id', '=', 'adv.id')
            ->join('wb_adv_v1_promotion_counts as pc', 'adv.advert_id', '=', 'pc.advert_id')
            ->where('adv.shop_id', $this->shop->id)
            ->where('p.date', $this->day)
            ->whereNotNull('p.good_id')
            ->where('pc.type', 9)
            ->whereExists(function($exists) {
                $exists->select(DB::raw(1))
                       ->from('wb_adv_v0_auction_adverts as aa')
                       ->whereColumn('aa.advert_id', 'adv.advert_id')
                       ->where('aa.bid_type', 'unified');
            })
            ->join('wb_adv_v0_auction_adverts as aa', function($join) {
                $join->on('adv.advert_id', '=', 'aa.advert_id')
                     ->on('aa.nm_id', '=', DB::raw('p.nm_id'));
            })
            ->select(
                'p.good_id',
                'p.sum',
                'p.views',
                'p.clicks',
                'p.orders'
            );

        $combinedQuery = DB::table(DB::raw("({$type8Query->toSql()} UNION ALL {$type9UnifiedQuery->toSql()}) as combined"))
            ->mergeBindings($type8Query)
            ->mergeBindings($type9UnifiedQuery);

        return $combinedQuery->select(
                'good_id',
                DB::raw('ROUND(SUM(sum)) as sum'),
                DB::raw('SUM(views) as views'),
                DB::raw('SUM(clicks) as clicks'),
                DB::raw('SUM(orders) as orders'),
                DB::raw('CASE WHEN SUM(views) > 0 THEN ROUND((SUM(sum) / SUM(views)) * 1000, 2) ELSE 0 END as cpm')
            )
            ->groupBy('good_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->good_id => [
                        'sum' => (int)$item->sum,
                        'views' => (int)$item->views,
                        'clicks' => (int)$item->clicks,
                        'orders' => (int)$item->orders,
                        'cpm' => (float)$item->cpm
                    ]
                ];
            })
            ->toArray();
    }

    private function getAucData(): array
    {
        $query = DB::table('wb_adv_v3_fs_products as p')
            ->join('wb_adv_v3_fs_apps as a', 'p.wb_adv_v3_fs_app_id', '=', 'a.id')
            ->join('wb_adv_v3_fs_days as d', 'a.wb_adv_v3_fs_day_id', '=', 'd.id')
            ->join('wb_adv_v3_fullstats_wb_adverts as adv', 'd.wb_adv_v3_fullstats_wb_advert_id', '=', 'adv.id')
            ->join('wb_adv_v1_promotion_counts as pc', 'adv.advert_id', '=', 'pc.advert_id')
            ->where('adv.shop_id', $this->shop->id)
            ->where('p.date', $this->day)
            ->whereNotNull('p.good_id')
            ->where('pc.type', 9)
            ->whereExists(function($exists) {
                $exists->select(DB::raw(1))
                       ->from('wb_adv_v0_auction_adverts as aa')
                       ->whereColumn('aa.advert_id', 'adv.advert_id')
                       ->where('aa.bid_type', 'manual');
            })
            ->join('wb_adv_v0_auction_adverts as aa', function($join) {
                $join->on('adv.advert_id', '=', 'aa.advert_id')
                     ->on('aa.nm_id', '=', DB::raw('p.nm_id'));
            });

        return $query->select(
                'p.good_id',
                DB::raw('ROUND(SUM(p.sum)) as sum'),
                DB::raw('SUM(p.views) as views'),
                DB::raw('SUM(p.clicks) as clicks'),
                DB::raw('SUM(p.orders) as orders'),
                DB::raw('CASE WHEN SUM(p.views) > 0 THEN ROUND((SUM(p.sum) / SUM(p.views)) * 1000, 2) ELSE 0 END as cpm')
            )
            ->groupBy('p.good_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->good_id => [
                        'sum' => (int)$item->sum,
                        'views' => (int)$item->views,
                        'clicks' => (int)$item->clicks,
                        'orders' => (int)$item->orders,
                        'cpm' => (float)$item->cpm
                    ]
                ];
            })
            ->toArray();
    }

    private function getAllAdvData(): array
    {
        return DB::table('wb_adv_v3_fs_products as p')
            ->where('p.date', $this->day)
            ->whereNotNull('p.good_id')
            ->select(
                'p.good_id',
                DB::raw('SUM(p.orders) as orders')
            )
            ->groupBy('p.good_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->good_id => [
                        'orders' => (int)$item->orders
                    ]
                ];
            })
            ->toArray();
    }
}
