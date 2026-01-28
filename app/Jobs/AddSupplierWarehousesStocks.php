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
        public array $chrtIdsData,
        public int $warehouseId
    ) {
        $this->shop = $shop;
        $this->date = $date;
        $this->chrtIdsData = $chrtIdsData;
        $this->warehouseId = $warehouseId;
    }

    public function handle(): void
    {
        $api = new WbApiService($this->shop->apiKey->key);

        $chrtIds = array_map(function ($chrtId) {
            return $chrtId;
        }, array_keys($this->chrtIdsData));

        $stocksData = $api->getApiV3Stocks($this->warehouseId, $chrtIds);

        if ($stocksData->isNotEmpty()) {
            SupplierWarehousesStocks::where('shop_id', $this->shop->id)
                ->where('date', $this->date)
                ->where('warehouse_id', $this->warehouseId)
                ->whereIn('chrt_id', $chrtIds)
                ->delete();

            $warehouse = $this->shop->warehouses()
                ->where('warehouse_id', $this->warehouseId)
                ->first();

            $stocksData->each(function ($stock) use ($warehouse) {
                if ($stock['amount'] != 0) {
                    $chrtId = $stock['chrtId'];

                    SupplierWarehousesStocks::create([
                        'shop_id' => $this->shop->id,
                        'date' => $this->date,
                        'office_id' => $warehouse->office_id,
                        'warehouse_name' => $warehouse->name,
                        'warehouse_id' => $this->warehouseId,
                        'nm_id' => $this->chrtIdsData[$chrtId]['nm_id'],
                        'vendor_code' => $this->chrtIdsData[$chrtId]['vendor_code'],
                        'chrt_id' => $chrtId,
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
