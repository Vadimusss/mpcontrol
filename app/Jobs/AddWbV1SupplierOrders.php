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

class AddWbV1SupplierOrders implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public string $day,
        public $timeout = 2400,
    )
    {
        $this->shop = $shop;
        $this->day = $day;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->shop->WbV1SupplierOrders()->whereRaw("DATE(date) >= '{$this->day}'")->delete();

        $api = new WbApiService($this->shop->apiKey->key);
        $api->getApiV1SupplierOrders($this->day)->
        map(function ($row) {
            $row['shop_id'] = $this->shop->id;
                return $row;
        })->
        chunk(1000)->
        each(function ($chunk) {
            $transformed = array_map([$this, 'camelToSnakeKeys'], $chunk->toArray());

            DB::table('wb_v1_supplier_orders')->insert($transformed);
        });

        
        // dump($transformed);

       // $this->shop->WbV1SupplierOrders()->createMany($transformed);

/*         $WbV1SupplierOrdersData->each(function ($row) {
            $good = Good::firstWhere('nm_id', $row['nmId']);
            if ($good !== null) {
                $good->WbV1SupplierOrders()->create([
                    'date' => $row['date'],
                    'last_change_date' => $row['lastChangeDate'],
                    'warehouse_name' => $row['warehouseName'],
                    'warehouse_type' => $row['warehouseType'],
                    'country_name' => $row['countryName'],
                    'oblast_okrug_name' => $row['oblastOkrugName'],
                    'region_name' => $row['regionName'],
                    'supplier_article' => $row['supplierArticle'],
                    'nm_id' => $row['nmId'],
                    'barcode' => $row['barcode'],
                    'category' => $row['category'],
                    'subject' => $row['subject'],
                    'brand' => $row['brand'],
                    'tech_size' => $row['techSize'],
                    'income_id' => $row['incomeID'],
                    'is_supply' => $row['isSupply'],
                    'is_realization' => $row['isRealization'],
                    'total_price' => $row['totalPrice'],
                    'discount_percent' => $row['discountPercent'],
                    'spp' => $row['spp'],
                    'finished_price' => $row['finishedPrice'],
                    'price_with_disc' => $row['priceWithDisc'],
                    'is_cancel' => $row['isCancel'],
                    'cancel_date' => $row['cancelDate'],
                    'order_type' => $row['orderType'],
                    'sticker' => $row['sticker'],
                    'g_number' => $row['gNumber'],
                    'srid' => $row['srid'],
                ]);
            }
        }); */
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
