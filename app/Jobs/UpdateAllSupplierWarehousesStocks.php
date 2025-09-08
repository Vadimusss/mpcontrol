<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Carbon;
use App\Events\JobFailed;
use Throwable;

class UpdateAllSupplierWarehousesStocks implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public ?string $date = null
    ) {
        $this->date = $date ?? date('Y-m-d');
    }

    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->with(['warehouses'])->get();

        $shops->each(function ($shop) {
            $warehouseIds = $shop->warehouses
                ->pluck('warehouse_id');
            
            $warehouseIds->each(function ($warehouseId) use ($shop) {
                $barcodesChunks = array_chunk($shop->barcodesWitchMetadata(), 1000, true);
                $jobsChain = array_map(function ($chunk) use ($shop, $warehouseId) {
                    return (new AddSupplierWarehousesStocks(
                        $shop,
                        $this->date,
                        $chunk,
                        $warehouseId))->delay(Carbon::now()->addMilliseconds(200));
                }, $barcodesChunks);

                Bus::chain($jobsChain)->dispatch();
            });
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateSupplierWarehousesStocks', $exception);
    }
}
