<?php

namespace App\Listeners;

use App\Events\ShopCreated;
use App\Services\WbApiService;
use App\Models\Good;
use App\Models\WbListGood;
use App\Models\WbListGoodSize;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateShopWbListGoodsData
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ShopCreated $event): void
    {
        $shop = $event->shop;
        $apiKey = $shop->apiKey->key;

        $api = new WbApiService($apiKey);
        $wbGoodListData = $api->getFullApiV2ListGoods();
        // dump($wbGoodListData);

        $wbGoodListData->each(function ($goodFromApi) use ($shop) {
            $good = $shop->goods()->create([
                'shop_id' => $shop->id,
                'nm_id' => $goodFromApi['nmID'],
                'vendor_code' => $goodFromApi['vendorCode'],
            ]);

            $wbListGood = $good->wbListGoodRow()->create([
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
    }
}
