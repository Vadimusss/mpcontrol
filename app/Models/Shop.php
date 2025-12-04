<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Shop extends Model
{
    protected $fillable = [
        'api_key_id',
        'name',
        'settings',
        'last_goods_data_update',
        'last_nsi_update',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    protected $with = ['owner', 'customers'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function apiKey(): HasOne
    {
        return $this->HasOne(ApiKey::class);
    }

    public function workSpaces(): HasMany
    {
        return $this->hasMany(WorkSpace::class);
    }

    public function goodLists(): HasMany
    {
        return $this->hasMany(GoodList::class);
    }

    public function goods(): HasMany
    {
        return $this->hasMany(Good::class);
    }

    public function WbListGood(): HasManyThrough
    {
        return $this->hasManyThrough(WbListGood::class, Good::class);
    }

    public function sizes(): HasManyThrough
    {
        return $this->hasManyThrough(WbListGoodSize::class, Good::class);
    }

    public function WbNmReportDetailHistory(): HasManyThrough
    {
        return $this->hasManyThrough(WbNmReportDetailHistory::class, Good::class);
    }

    public function wbAnalyticsV3ProductsHistory(): HasManyThrough
    {
        return $this->hasManyThrough(WbAnalyticsV3ProductsHistory::class, Good::class);
    }

    public function WbAdvV1Upd(): HasManyThrough
    {
        return $this->hasManyThrough(WbAdvV1Upd::class, Good::class);
    }

    public function WbV1SupplierOrders(): HasMany
    {
        return $this->hasMany(WbV1SupplierOrders::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(WbV1SupplierStocks::class);
    }

    public function salesFunnel(): HasManyThrough
    {
        return $this->hasManyThrough(SalesFunnel::class, Good::class);
    }

    public function stocksAndOrders(): HasMany
    {
        return $this->hasMany(StocksAndOrders::class);
    }

    public function wbAdvV1PromotionCounts(): HasMany
    {
        return $this->hasMany(WbAdvV1PromotionCount::class);
    }

    public function wbAdvV2FullstatsWbAdverts(): HasMany
    {
        return $this->hasMany(WbAdvV2FullstatsWbAdvert::class);
    }

    public function wbAdvV3FullstatsWbAdverts(): HasMany
    {
        return $this->hasMany(WbAdvV3FullstatsWbAdvert::class);
    }

    public function nsis(): HasManyThrough
    {
        return $this->hasManyThrough(Nsi::class, Good::class);
    }

    public function TempWbNmReportDetailHistory(): HasMany
    {
        return $this->hasMany(TempWbNmReportDetailHistory::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(WbApiV3Warehouses::class);
    }

    public function supplierWarehousesStocks(): HasMany
    {
        return $this->hasMany(SupplierWarehousesStocks::class);
    }

    public function wbAdvV1PromotionAdverts(): HasMany
    {
        return $this->hasMany(WbAdvV1PromotionAdverts::class);
    }

    public function wbAdvV0AuctionAdvert(): HasMany
    {
        return $this->hasMany(WbAdvV0AuctionAdvert::class);
    }

    public function wbContentV2CardsListSizes(): HasManyThrough
    {
        return $this->hasManyThrough(
            WbContentV2CardsListSize::class,
            WbContentV2CardsList::class,
            'shop_id',
            'wb_cards_list_id',
            'id',
            'id',
        );
    }

    public function barcodes(): array
    {
        $barcodes = $this->wbContentV2CardsListSizes->reduce(function (array $skus, $size) {
            $sizeSkus = array_map('trim', explode(',', $size->skus_text));
            return $skus = array_merge($skus, $sizeSkus);
        }, []);

        return array_unique($barcodes);
    }

    public function barcodesWitchMetadata(): array
    {
        $barcodesWithMetadata = [];

        $sizes = $this->wbContentV2CardsListSizes()
            ->with(['cardsList' => function ($query) {
                $query->select('id', 'nm_id', 'vendor_code');
            }])
            ->get(['wb_cards_sizes.id', 'wb_cards_sizes.skus_text', 'wb_cards_sizes.wb_cards_list_id']);

        foreach ($sizes as $size) {
            if (!$size->cardsList) {
                continue;
            }

            $barcodes = array_map('trim', explode(',', $size->skus_text));

            $nmId = $size->cardsList->nm_id;
            $vendorCode = $size->cardsList->vendor_code;

            foreach ($barcodes as $barcode) {
                if (!empty($barcode)) {
                    $barcodesWithMetadata[$barcode] = [
                        'nm_id' => $nmId,
                        'vendor_code' => $vendorCode,
                    ];
                }
            }
        }

        return $barcodesWithMetadata;
    }
}
