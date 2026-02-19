<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Good extends Model
{
    protected $fillable = [
        'shop_id',
        'nm_id',
        'vendor_code',
        'good_status_id',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(GoodList::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(GoodStatus::class, 'good_status_id', 'id');
    }

    public function wbListGoodRow(): HasOne
    {
        return $this->HasOne(WbListGood::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(WbListGoodSize::class);
    }

    public function WbNmReportDetailHistory(): HasMany
    {
        return $this->hasMany(WbNmReportDetailHistory::class);
    }

    public function WbAdvV1Upd(): HasMany
    {
        return $this->hasMany(WbAdvV1Upd::class);
    }

    public function salesFunnel(): HasMany
    {
        return $this->hasMany(SalesFunnel::class);
    }

    public function wbAdvV2FullstatsProducts(): HasMany
    {
        return $this->hasMany(WbAdvV2FullstatsProduct::class);
    }

    public function nsi(): HasOne
    {
        return $this->HasOne(Nsi::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function wbAnalyticsV3ProductsHistory(): HasMany
    {
        return $this->hasMany(WbAnalyticsV3ProductsHistory::class);
    }

    public function internalNsi(): HasOne
    {
        return $this->hasOne(InternalNsi::class);
    }
}
