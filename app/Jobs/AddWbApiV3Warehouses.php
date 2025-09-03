<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use Throwable;

class AddWbApiV3Warehouses implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop
    ) {
        $this->shop = $shop;
    }

    public function handle(): void
    {
        $api = new WbApiService($this->shop->apiKey->key);
        $warehousesData = $api->getApiV3Warehouses();

        if ($warehousesData->isNotEmpty()) {
            // Очищаем старые данные складов для этого магазина
            $this->shop->warehouses()->delete();

            $warehousesData->each(function ($warehouse) {
                $this->shop->warehouses()->create([
                    'name' => $warehouse['name'],
                    'office_id' => $warehouse['officeId'],
                    'warehouse_id' => $warehouse['id'],
                    'cargo_type' => $warehouse['cargoType'],
                    'delivery_type' => $warehouse['deliveryType'],
                    'is_deleting' => $warehouse['isDeleting'],
                    'is_processing' => $warehouse['isProcessing'],
                ]);
            });
        }
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('AddWbApiV3Warehouses', $exception);
    }
}
