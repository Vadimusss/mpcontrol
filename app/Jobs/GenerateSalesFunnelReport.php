<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\Shop;

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

        $shops = Shop::without(['owner', 'customers'])->with([
            'WbNmReportDetailHistory' => function (Builder $query) use ($day) {
                $query->where('dt', '=', $day);
            },
            'WbAdvV1Upd' => function (Builder $query) use ($day) {
                $query->whereRaw("DATE(upd_time) = '{$day}'");
            },
            'WbV1SupplierOrders' => function (Builder $query) use ($day) {
                $query->where('date', 'like', "%{$day}%");
            },
            ])->get();

        $shops->each(function ($shop, int $key) {
            $advCostsSumByGoodId = $shop->WbAdvV1Upd->groupBy('good_id')->reduce(function ($carry, $day, $goodId) {
                $carry[$goodId] = $day->groupBy('advert_type')->reduce(function ($acc, $row, $advType) use ($goodId) {
                    $acc += $row->max('upd_sum');
                    return $acc;
                }, 0);
                return $carry;
            }, []);

            $WbV1SupplierOrders = $shop->WbV1SupplierOrders->select(
                'good_id',
                'finished_price',
                'price_with_disc',
            )->groupBy('good_id')->reduce(function ($carry, $day, $goodId) {
                $carry[$goodId]['finished_price'] = round($day->avg('finished_price'), 2);
                $carry[$goodId]['price_with_disc'] = round($day->avg('price_with_disc'), 2);
                return $carry;
            }, []);

            $report = $shop->WbNmReportDetailHistory->select(
                'good_id',
                'vendor_code',
                'nm_id',
                'imt_name',
                'date',
                'open_card_count',
                'add_to_cart_count',
                'orders_count',
                'orders_sum_rub')->map(function ($row) use ($advCostsSumByGoodId, $WbV1SupplierOrders) {
                    $row['advertising_costs'] = array_key_exists($row['good_id'], $advCostsSumByGoodId) ? $advCostsSumByGoodId[$row['good_id']] : 0;
                    $row['finished_price'] = array_key_exists($row['good_id'], $WbV1SupplierOrders) ? $WbV1SupplierOrders[$row['good_id']]['finished_price'] : 0;
                    $row['price_with_disc'] = array_key_exists($row['good_id'], $WbV1SupplierOrders) ? $WbV1SupplierOrders[$row['good_id']]['price_with_disc'] : 0;
                    return $row;
                });

            dump($report);
        });
    }
}
