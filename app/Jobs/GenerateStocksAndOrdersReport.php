<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\DB;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Throwable;

class GenerateStocksAndOrdersReport implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public $day = null,
        public $timeout = 2400,
    )
    {
        $this->day = $day ?? date('Y-m-d', time());
        $this->shop = $shop;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $stocks = $this->shop->stocks()->where('date', '=', $this->day)->select(
            'shop_id',
            'barcode',
            'supplier_article',
            'nm_id',
            'warehouse_name',
            'quantity',
            'date')->get();

        if ($stocks->isNotEmpty()) {
            $this->shop->stocksAndOrders()->where('date', '=', $this->day)->delete();

            $stocks->chunk(1000)->each(function ($chunk) {
                DB::table('stocks_and_orders')->insert($chunk->toArray());
            });

            $message = "Остатки и продажи магазина {$this->shop->name} за {$this->day} обновлены!";
        } else {
            $message = "Нет данных об остатках магазина {$this->shop->name} за {$this->day}, отчет не обновлен.";
        }

        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('GenerateStocksAndOrdersReport', $duration, $message);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('GenerateStocksAndOrdersReport', $exception);
    }
}
