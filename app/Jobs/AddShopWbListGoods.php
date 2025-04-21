<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\Good;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\JobFailed;
use Throwable;
use Illuminate\Support\Facades\Log;

class AddShopWbListGoods implements ShouldQueue
{
    use Queueable;

    public function __construct(public Shop $shop, public $timeout = 600)
    {
        $this->shop = $shop->withoutRelations();
    }

    public function handle(): void
    {
        $shop = $this->shop;
        $apiKey = $shop->apiKey->key;

        $api = new WbApiService($apiKey);
        $wbGoodListData = $api->getFullApiV2ListGoods();

        $wbGoodListData->each(function ($goodFromApi) use ($shop) {
            $existingByNmId = Good::with('wbListGoodRow')
                ->where('shop_id', $shop->id)
                ->where('nm_id', $goodFromApi['nmID'])
                ->first();

            $existingByVendorCode = Good::with('wbListGoodRow')
                ->where('shop_id', $shop->id)
                ->where('vendor_code', $goodFromApi['vendorCode'])
                ->where('nm_id', '!=', $goodFromApi['nmID'])
                ->first();

            if ($existingByVendorCode) {
                $existingByVendorCode->update([
                    'vendor_code' => $existingByVendorCode->vendor_code . ' archived (' . now()->format('d.m.Y') . ')'
                ]);
                $existingByVendorCode->wbListGoodRow()->update([
                    'vendor_code' => $goodFromApi['vendorCode']
                ]);
            } elseif ($existingByNmId) {
                $existingByNmId->update(['vendor_code' => $goodFromApi['vendorCode']]);
                $existingByNmId->wbListGoodRow()->update([
                    'vendor_code' => $goodFromApi['vendorCode']
                ]);
            } else {
                $good = $shop->goods()->create([
                    'shop_id' => $shop->id,
                    'nm_id' => $goodFromApi['nmID'],
                    'vendor_code' => $goodFromApi['vendorCode'],
                ]);

                $good->wbListGoodRow()->create([
                    'good_id' => $good->id,
                    'nm_id' => $goodFromApi['nmID'],
                    'vendor_code' => $goodFromApi['vendorCode'],
                    'currency_iso_code_4217' => $goodFromApi['currencyIsoCode4217'],
                    'discount' => $goodFromApi['discount'],
                    'club_discount' => $goodFromApi['clubDiscount'],
                    'editable_size_price' => $goodFromApi['editableSizePrice'],
                ]);

                collect($goodFromApi['sizes'])->each(function ($size) use ($good) {
                    $good->sizes()->create([
                        'good_id' => $good->id,
                        'size_id' => $size['sizeID'],
                        'price' => $size['price'],
                        'discounted_price' => $size['discountedPrice'],
                        'club_discounted_price' => $size['clubDiscountedPrice'],
                        'tech_size_name' => $size['techSizeName'],
                    ]);
                });
            }
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('AddShopWbListGoods', $exception);
    }
}
