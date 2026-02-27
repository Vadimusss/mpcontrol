<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\DB;
use App\Events\JobSucceeded;
use App\Events\JobFailed;
use Throwable;

class UpdateStocksAndOrdersReport implements ShouldQueue
{
    use Queueable, Batchable;

    public function __construct(public Shop $shop, public $date = null)
    {
        $this->date = $date ?? date('Y-m-d', time());
        $this->shop = $shop;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $stocks = $this->shop->stocks()->
            select('shop_id', 'barcode', 'supplier_article', 'nm_id', 'warehouse_name', 'quantity', 'date')->
            where('date', '=', $this->date)->get();

        if ($stocks->isNotEmpty()) {
            $this->shop->stocksAndOrders()->where('date', '=', $this->date)->delete();

            $wbV1SupplierOrders = $this->shop->WbV1SupplierOrders()->
                select('warehouse_name', 'barcode')->
                whereRaw("DATE(date) = '{$this->date}'")->get();

            $ordersCount = $wbV1SupplierOrders->groupBy('warehouse_name')->map(function ($warehouse) {
                return $warehouse->countBy('barcode');
            })->toArray();

            $stocks->map(function ($row) use ($ordersCount) {
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

            $message = "Остатки и продажи магазина {$this->shop->name} за {$this->date} обновлены!";
        } else {
            $message = "Нет данных об остатках магазина {$this->shop->name} за {$this->date}, отчет не обновлен.";
        }

        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('UpdateStocksAndOrdersReport', $duration, $message);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateStocksAndOrdersReport', $exception);
    }
}
