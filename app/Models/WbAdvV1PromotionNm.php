<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbAdvV1PromotionNm extends Model
{
    protected $table = 'wb_adv_v1_promo_nms';

    protected $fillable = [
        'wb_adv_v1_promotion_adverts_id',
        'nm',
    ];

    public function advert(): BelongsTo
    {
        return $this->belongsTo(WbAdvV1PromotionAdverts::class, 'wb_adv_v1_promotion_adverts_id');
    }
}
