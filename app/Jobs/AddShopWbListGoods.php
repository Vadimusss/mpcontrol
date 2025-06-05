<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\Good;
use App\Services\WbApiService;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use Throwable;

class AddShopWbListGoods implements ShouldQueue
{
    use Batchable, Queueable;

    private WbApiService $api;

    public function __construct(public Shop $shop, public $timeout = 600)
    {
        $this->shop = $shop->withoutRelations();
        $this->api = new WbApiService($shop->apiKey->key);
    }

    public function handle(): void
    {
        $wbGoodListData = $this->api->getFullApiV2ListGoods();

        $wbGoodListData->each(function ($goodFromApi) {
            $existingByNmId = Good::with('wbListGoodRow')
                ->where('shop_id', $this->shop->id)
                ->where('nm_id', $goodFromApi['nmID'])
                ->first();

            $existingByVendorCode = Good::with('wbListGoodRow')
                ->where('shop_id', $this->shop->id)
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
                $this->updateGoodRelations($existingByVendorCode, $goodFromApi);
            } elseif ($existingByNmId) {
                $existingByNmId->update(['vendor_code' => $goodFromApi['vendorCode']]);
                $this->updateGoodRelations($existingByNmId, $goodFromApi);
            } else {
                $this->createNewGood($this->shop, $goodFromApi);
            }
        });
    }

    private function updateGoodRelations(Good $good, array $goodFromApi): void
    {
        $good->wbListGoodRow()->delete();
        $good->wbListGoodRow()->create([
            'good_id' => $good->id,
            'nm_id' => $goodFromApi['nmID'],
            'vendor_code' => $goodFromApi['vendorCode'],
            'currency_iso_code_4217' => $goodFromApi['currencyIsoCode4217'],
            'discount' => $goodFromApi['discount'],
            'club_discount' => $goodFromApi['clubDiscount'],
            'editable_size_price' => $goodFromApi['editableSizePrice'],
        ]);

        $good->sizes()->delete();
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

    private function createNewGood(Shop $shop, array $goodFromApi): Good
    {
        $good = $shop->goods()->create([
            'shop_id' => $shop->id,
            'nm_id' => $goodFromApi['nmID'],
            'vendor_code' => $goodFromApi['vendorCode'],
        ]);

        $this->updateGoodRelations($good, $goodFromApi);

        return $good;
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('AddShopWbListGoods', $exception);
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }
}
