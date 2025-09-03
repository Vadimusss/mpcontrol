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
        public array $barcodes,
        public int $warehouseId
    ) {
        $this->shop = $shop;
        $this->date = $date;
        $this->barcodes = $barcodes;
        $this->warehouseId = $warehouseId;
    }

    public function handle(): void
    {
        $api = new WbApiService($this->shop->apiKey->key);
        $stocksData = $api->getApiV3Stocks($this->warehouseId, $this->barcodes);

        if ($stocksData->isNotEmpty()) {
            SupplierWarehousesStocks::where('shop_id', $this->shop->id)
                ->where('date', $this->date)
                ->whereIn('barcode', $this->barcodes)
                ->delete();

            $warehouse = $this->shop->warehouses()
                ->where('warehouse_id', $this->warehouseId)
                ->first();

            $warehouseName = $warehouse ? $warehouse->name : 'Unknown Warehouse';

            $stocksData->each(function ($stock) use ($warehouse) {
                SupplierWarehousesStocks::create([
                    'shop_id' => $this->shop->id,
                    'date' => $this->date,
                    'office_id' => $warehouse->office_id,
                    'warehouse_name' => $warehouse->name,
                    'warehouse_id' => $this->warehouseId,
                    'barcode' => $stock['sku'],
                    'amount' => $stock['amount'],
                ]);
            });
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->dispatchNextJobInChain();
        JobFailed::dispatch('AddSupplierWarehousesStocks', $exception);
    }
}
