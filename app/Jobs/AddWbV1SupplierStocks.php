<?php

namespace App\Jobs;

use Illuminate\Support\Str;
use App\Models\Shop;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Throwable;

class AddWbV1SupplierStocks implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public string $date,
        public string $dateFrom = '2023-06-01',
    ) {
        $this->shop = $shop;
        $this->date = $date;
        $this->dateFrom = $dateFrom;
    }

    public $timeout = 240;
    public $backoff = 60;
    public $tries = 2;

    public function handle(): void
    {
        $startTime = microtime(true);

        $api = new WbApiService($this->shop->apiKey->key);
        $newData = $api->getApiV1SupplierStocks($this->dateFrom)->map(function ($row) {
                $row['shop_id'] = $this->shop->id;
                $row['date'] = $this->date;
                return $row;
            });

        if ($newData->isNotEmpty()) {
            $this->shop->stocks()->whereRaw("DATE(date) = '{$this->date}'")->delete();

            $newData->chunk(1000)->each(function ($chunk) {
                $transformed = array_map([$this, 'camelToSnakeKeys'], $chunk->toArray());

                DB::table('wb_v1_supplier_stocks')->insert($transformed);
            });
        }

        $message = $message = "Остатки FBO магазина {$this->shop->name} за {$this->date} обновлены!";
        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('AddWbV1SupplierStocks', $duration, $message);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('AddWbV1SupplierStocks', $exception);
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
