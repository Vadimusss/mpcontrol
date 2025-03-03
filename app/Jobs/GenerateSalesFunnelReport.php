<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Models\Good;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class GenerateSalesFunnelReport implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Shop $shop,
        public string $day,
        public $timeout = 1200,
    )
    {
        $this->day = $day;
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // $shop = Shop::find(117);
        // $day = '2025-01-28';

        $this->shop->salesFunnel()->where('date', '=', $this->day)->delete();

        $WbNmReportDetailHistory = $this->shop->WbNmReportDetailHistory()->
            select(
                'good_id',
                'wb_nm_report_detail_histories.vendor_code',
                'wb_nm_report_detail_histories.nm_id',
                'imt_name',
                'dt',
                'open_card_count',
                'add_to_cart_count',
                'orders_count', 'orders_sum_rub')->
            where('dt', '=', $this->day)->get();

        $WbAdvV1Upd = $this->shop->WbAdvV1Upd()->
            select('good_id', 'upd_sum')->where('upd_time', 'like', "%{$this->day}%")->get();
        
        $WbV1SupplierOrders = $this->shop->WbV1SupplierOrders()->
            select('nm_id', 'finished_price', 'price_with_disc')->where('date', 'like', "%{$this->day}%")->get();

        $advCostsSumByGoodId = $WbAdvV1Upd->groupBy('good_id')->reduce(function ($carry, $day, $goodId) {
            $carry[$goodId] = $day->sum('upd_sum');
            return $carry;
        }, []);

        $avgPricesByDay = $WbV1SupplierOrders->groupBy('nm_id')->reduce(function ($carry, $day, $nmId) {
            $carry[$nmId]['finished_price'] = round($day->avg('finished_price'), 2);
            $carry[$nmId]['price_with_disc'] = round($day->avg('price_with_disc'), 2);
            return $carry;
        }, []);

        $report = $WbNmReportDetailHistory->map(function ($row) use ($advCostsSumByGoodId, $avgPricesByDay) {
            $row->advertising_costs = array_key_exists($row->good_id, $advCostsSumByGoodId) ? $advCostsSumByGoodId[$row->good_id] : 0;
            $row->finished_price = array_key_exists($row->nm_id, $avgPricesByDay) ? $avgPricesByDay[$row->nm_id]['finished_price'] : 0;
            $row->price_with_disc = array_key_exists($row->nm_id, $avgPricesByDay) ? $avgPricesByDay[$row->nm_id]['price_with_disc'] : 0;
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
            ]);
        });
    }
}
