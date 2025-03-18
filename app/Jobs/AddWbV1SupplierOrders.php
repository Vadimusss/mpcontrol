<?php

namespace App\Jobs;

use App\Models\Good;
use App\Models\Shop;
use App\Services\WbApiService;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use Throwable;

class AddWbV1SupplierOrders implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public string $date,
        public $timeout = 2400,
    ) {
        $this->shop = $shop;
        $this->date = $date;
    }

    public function handle(): void
    {
        $this->shop->WbV1SupplierOrders()->whereRaw("DATE(date) >= '{$this->date}'")->delete();

        $api = new WbApiService($this->shop->apiKey->key);
        $api->getApiV1SupplierOrders($this->date)->map(function ($row) {
                $row['shop_id'] = $this->shop->id;
                return $row;
            })->chunk(1000)->each(function ($chunk) {
                $transformed = array_map([$this, 'camelToSnakeKeys'], $chunk->toArray());

                DB::table('wb_v1_supplier_orders')->insert($transformed);
            });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('AddWbV1SupplierOrders', $exception);
    }

    protected function camelToSnakeKeys($array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $tmp = preg_replace('/([A-Z]+)/', '_$1', $key);
            $tmp = str_replace('I_D', 'ID', $tmp);
            $tmp = Str::lower($tmp);
            $newKey = ltrim($tmp, '_');

            $result[$newKey] = $value;
        }
        return $result;
    }
}
