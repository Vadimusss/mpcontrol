<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Good;
use Illuminate\Support\Facades\DB;

class GenerateSalesFunnelReport implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $daysAgo = 0)
    {
        $this->daysAgo = $daysAgo;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $day = date('Y-m-d',strtotime("-{$this->daysAgo} days"));

        $WbNmReportDetailHistory = DB::table('wb_nm_report_detail_histories')->select(
                'good_id',
                'vendor_code',
                'nm_id',
                'imt_name',
                'dt',
                'open_card_count',
                'add_to_cart_count',
                'orders_count',
                'orders_sum_rub')->where('dt', '=', $day)->get();

        $WbAdvV1Upd = DB::table('wb_adv_v1_upds')->select(
                'good_id',
                'upd_sum',
                'advert_type')->where('upd_time', 'like', "%{$day}%")->get();
        
        $WbV1SupplierOrders = DB::table('wb_v1_supplier_orders')->select(
                'good_id',
                'finished_price',
                'price_with_disc')->where('date', 'like', "%{$day}%")->get();
        
        $advCostsSumByGoodId = $WbAdvV1Upd->groupBy('good_id')->reduce(function ($carry, $day, $goodId) {
            $carry[$goodId] = $day->groupBy('advert_type')->reduce(function ($acc, $row, $advType) use ($goodId) {
                $acc += $row->max('upd_sum');
                return $acc;
            }, 0);
            return $carry;
        }, []);

        $avgPricesByDay = $WbV1SupplierOrders->groupBy('good_id')->reduce(function ($carry, $day, $goodId) {
            $carry[$goodId]['finished_price'] = round($day->avg('finished_price'), 2);
            $carry[$goodId]['price_with_disc'] = round($day->avg('price_with_disc'), 2);
            return $carry;
        }, []);

        $report = $WbNmReportDetailHistory->map(function ($row) use ($advCostsSumByGoodId, $avgPricesByDay) {
            $row->advertising_costs = array_key_exists($row->good_id, $advCostsSumByGoodId) ? $advCostsSumByGoodId[$row->good_id] : 0;
            $row->finished_price = array_key_exists($row->good_id, $avgPricesByDay) ? $avgPricesByDay[$row->good_id]['finished_price'] : 0;
            $row->price_with_disc = array_key_exists($row->good_id, $avgPricesByDay) ? $avgPricesByDay[$row->good_id]['price_with_disc'] : 0;
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
