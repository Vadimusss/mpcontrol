<?php

namespace App\Jobs;

use Illuminate\Support\Str;
use App\Models\Shop;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;

class UpdateWbV1SupplierStocks implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Shop $shop,
        public string $dateFrom = '2023-06-01',
        public $timeout = 1200,
    )
    {
        $this->shop = $shop;
        $this->dateFrom = $dateFrom;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->shop->stocks()->delete();
        $api = new WbApiService($this->shop->apiKey->key);
        $WbV1SupplierStocks = $api->getApiV1SupplierStocks($this->dateFrom)->toArray();

        $transformed = array_map([$this, 'camelToSnakeKeys'], $WbV1SupplierStocks);

        $this->shop->stocks()->createMany($transformed);
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
