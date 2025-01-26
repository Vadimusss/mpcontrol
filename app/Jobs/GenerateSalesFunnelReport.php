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
                $query->select(
                    'good_id',
                    'wb_nm_report_detail_histories.nm_id',
                    'imt_name',
                    'wb_nm_report_detail_histories.vendor_code',
                    'dt',
                    'open_card_count',
                    'add_to_cart_count',
                    'orders_count',
                    'orders_sum_rub')->where('dt', '=', $day);
            },
            'WbAdvV1Upd' => function (Builder $query)  use ($day) {
                $query->whereRaw("upd_time = '{$day}'");
            },
            'WbV1SupplierOrders' => function (Builder $query)  use ($day) {
                $query->where('date', '=', $day);
            },
            ])->take(1)->get();

        $shops->each(function ($shop, int $key) {
            $WbNmReportDetailHistory = $shop->WbNmReportDetailHistory()->get()->toArray();
            $WbAdvV1Upd = $shop->WbAdvV1Upd()->get()->toArray();
            $WbV1SupplierOrders = $shop->WbV1SupplierOrders()->get()->toArray();

            dump($WbNmReportDetailHistory);
        });
    }
}
