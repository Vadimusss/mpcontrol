<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\SupplierWarehousesStocks;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use Throwable;

class AddSupplierWarehousesStocks implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public string $date,
        public array $barcodesData,
        public int $warehouseId
    ) {
        $this->shop = $shop;
        $this->date = $date;
        $this->barcodesData = $barcodesData;
        $this->warehouseId = $warehouseId;
    }

    public $timeout = 240;
    public $backoff = 60;
    public $tries = 3;

    public function handle(): void
    {
        $api = new WbApiService($this->shop->apiKey->key);

        $stringBarcodes = array_map(function ($barcode) {
            return (string) $barcode;
        }, array_keys($this->barcodesData));

        $stocksData = $api->getApiV3Stocks($this->warehouseId, $stringBarcodes);

        if ($stocksData->isNotEmpty()) {
            SupplierWarehousesStocks::where('shop_id', $this->shop->id)
                ->where('date', $this->date)
                ->where('warehouse_id', $this->warehouseId)
                ->whereIn('barcode', $stringBarcodes)
                ->delete();

            $warehouse = $this->shop->warehouses()
                ->where('warehouse_id', $this->warehouseId)
                ->first();

            $stocksData->each(function ($stock) use ($warehouse) {
                if ($stock['amount'] != 0) {
                    $barcode = $stock['sku'];

                    SupplierWarehousesStocks::create([
                        'shop_id' => $this->shop->id,
                        'date' => $this->date,
                        'office_id' => $warehouse->office_id,
                        'warehouse_name' => $warehouse->name,
                        'warehouse_id' => $this->warehouseId,
                        'nm_id' => $this->barcodesData[$barcode]['nm_id'] ?? null,
                        'vendor_code' => $this->barcodesData[$barcode]['vendor_code'] ?? null,
                        'barcode' => $stock['sku'],
                        'amount' => $stock['amount'],
                    ]);
                }
            });
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->dispatchNextJobInChain();
        JobFailed::dispatch('AddSupplierWarehousesStocks', $exception);
    }
}
