<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\DB;

class UpdateStocksAndOrdersReport implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Shop $shop, public $date = null)
    {
        $this->date = $date ?? date('Y-m-d', time());
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $stocksAndOrdersReport = $this->shop->stocksAndOrders()->
            select('shop_id', 'barcode', 'supplier_article', 'nm_id', 'warehouse_name', 'date', 'quantity', 'orders_count')->
                where('date', '=', $this->date)->get();

        $this->shop->stocksAndOrders()->where('date', '=', $this->date)->delete();

        $wbV1SupplierOrders = $this->shop->WbV1SupplierOrders()->
            select('date', 'warehouse_name', 'barcode')->
                where('date', 'like', "%{$this->date}%")->get();

        $ordersCount = $wbV1SupplierOrders->groupBy('warehouse_name')->map(function ($warehouse) {
            return $warehouse->countBy('barcode');
        })->toArray();

        $stocksAndOrdersReport->map(function ($row) use ($ordersCount) {
            $warehouseName = $row->warehouse_name;
            $barcode = $row->barcode;
            $row->orders_count = array_key_exists($warehouseName, $ordersCount) &&
                array_key_exists($barcode, $ordersCount[$warehouseName]) ? $ordersCount[$warehouseName][$barcode] : 0;

            return $row;
        })->
        chunk(1000)->
        each(function ($chunk) {
            DB::table('stocks_and_orders')->insert($chunk->toArray());
        });
    }
}
