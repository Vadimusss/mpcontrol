<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;

class FixSalesFunelReport implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Shop $shop,
        public string $date,
        public $timeout = 2400,
    )
    {
        $this->shop = $shop;
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $WbAdvV1Upd = $this->shop->WbAdvV1Upd()->
            select('good_id', 'upd_sum')->where('upd_time', 'like', "%{$this->date}%")->get();

        $advCostsSumByGoodId = $WbAdvV1Upd->groupBy('good_id')->reduce(function ($carry, $day, $goodId) {
            $carry[$goodId] = $day->sum('upd_sum');
            return $carry;
        }, []);

        $salesFunnelReport = $this->shop->salesFunnel()->where('date', '=', $this->date)->get();

        $salesFunnelReport->each(function ($row) use ($advCostsSumByGoodId) {
            if (array_key_exists($row->good_id, $advCostsSumByGoodId)) {
                $row->advertising_costs = $advCostsSumByGoodId[$row->good_id];
            }
            $row->save();
        });
    }
}
