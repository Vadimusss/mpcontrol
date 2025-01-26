<?php

namespace App\Jobs;

use App\Models\Good;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;

class AddWbV1SupplierOrders implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public string $apiKey,
        public string $dateFrom,
        public $timeout = 600,
    )
    {
        $this->apiKey = $apiKey;
        $this->dateFrom = $dateFrom;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $api = new WbApiService($this->apiKey);
        $WbV1SupplierOrdersData = $api->getApiV1SupplierOrders($this->dateFrom);

        $WbV1SupplierOrdersData->each(function ($row) {
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
        });
    }
}
