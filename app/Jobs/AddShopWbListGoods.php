<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\Good;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\JobFailed;
use Throwable;

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
            Good::where('shop_id', '=', $shop->id)->where('nm_id', '=', $goodFromApi['nmID'])->firstOr(function () use ($goodFromApi, $shop) {

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
            });
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('AddShopWbListGoods', $exception);
    }
}
