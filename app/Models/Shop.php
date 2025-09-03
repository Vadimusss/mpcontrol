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
}
