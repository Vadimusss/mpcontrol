<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\DB;

class GenerateStocksAndOrdersReport implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Shop $shop,
        public $day = null,
        public $timeout = 2400,
    )
    {
        $this->day = $day ?? date('Y-m-d', time());
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->shop->stocksAndOrders()->where('date', '=', $this->day)->delete();

        $this->shop->stocks()->select(
            'shop_id',
            'barcode',
            'supplier_article',
            'nm_id',
            'warehouse_name',
            'quantity')->
        get()->
        map(function ($row) {
            $row['date'] = $this->day;
                return $row;
        })->
        chunk(1000)->
        each(function ($chunk) {
            DB::table('stocks_and_orders')->insert($chunk->toArray());
        });

        // $this->shop->stocksAndOrders()->createMany($shopStocks);
    }
}
