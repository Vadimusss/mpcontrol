<?php

namespace App\Jobs;

use Illuminate\Support\Str;
use App\Models\Shop;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use Throwable;

class UpdateWbV1SupplierStocks implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public string $dateFrom = '2023-06-01',
    ) {
        $this->shop = $shop;
        $this->dateFrom = $dateFrom;
    }

    public $timeout = 120;
    public $backoff = 1;
    public $tries = 5;

    public function handle(): void
    {
        $this->shop->stocks()->delete();
        $api = new WbApiService($this->shop->apiKey->key);
        $WbV1SupplierStocks = $api->getApiV1SupplierStocks($this->dateFrom)->toArray();

        $transformed = array_map([$this, 'camelToSnakeKeys'], $WbV1SupplierStocks);

        $this->shop->stocks()->createMany($transformed);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateWbV1SupplierStocks', $exception);
    }

    protected function camelToSnakeKeys($array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = Str::snake($key);
            $result[$newKey] = $value;
        }
        return $result;
    }
}
