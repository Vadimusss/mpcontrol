<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WbAdvV2FullstatsWbAdvert extends Model
{
    protected $fillable = [
        'shop_id',
        'views',
        'clicks',
        'ctr',
        'cpc',
        'sum',
        'atbs',
        'orders',
        'cr',
        'shks',
        'sum_price',
        'date',
        'advert_id'
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function wbAdvV2FullstatsDays(): HasMany
    {
        return $this->hasMany(WbAdvV2FullstatsDay::class);
    }

    public function wbAdvV1PromotionCount(): BelongsTo
    {
        return $this->belongsTo(WbAdvV1PromotionCount::class, 'advert_id', 'advert_id');
    }
}
