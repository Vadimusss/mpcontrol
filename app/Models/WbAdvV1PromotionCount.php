<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WbAdvV1PromotionCount extends Model
{
    protected $fillable = [
        'shop_id',
        'type', 
        'status',
        'advert_id',
        'change_time'
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function wbAdvV2FullstatsWbAdverts(): HasMany
    {
        return $this->hasMany(WbAdvV2FullstatsWbAdvert::class, 'advert_id', 'advert_id');
    }
}
