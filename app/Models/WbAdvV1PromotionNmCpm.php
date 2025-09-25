<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbAdvV1PromotionNmCpm extends Model
{
    protected $table = 'wb_adv_v1_promo_nm_cpm';

    protected $fillable = [
        'wb_adv_v1_promotion_adverts_id',
        'nm',
        'cpm',
    ];

    protected $casts = [
        'cpm' => 'integer',
    ];

    public function advert(): BelongsTo
    {
        return $this->belongsTo(WbAdvV1PromotionAdverts::class, 'wb_adv_v1_promotion_adverts_id');
    }
}
