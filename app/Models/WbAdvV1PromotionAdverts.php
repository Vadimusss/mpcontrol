<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WbAdvV1PromotionAdverts extends Model
{
    protected $table = 'wb_adv_v1_promotion_adverts';

    protected $fillable = [
        'shop_id',
        'advert_id',
        'name',
        'type',
        'status',
        'payment_type',
        'bid_type',
        'daily_budget',
        'start_time',
        'end_time',
        'create_time',
        'change_time',
        'cpm',
        'subject_id',
        'subject_name',
        'active_carousel',
        'active_recom',
        'active_booster',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'create_time' => 'datetime',
        'change_time' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function wbAdvV1PromotionAdvertsNms(): HasMany
    {
        return $this->hasMany(WbAdvV1PromotionNm::class);
    }

    public function wbAdvV1PromotionAdvertsNmCpm(): HasMany
    {
        return $this->hasMany(WbAdvV1PromotionNmCpm::class);
    }
}
