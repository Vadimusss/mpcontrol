<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbApiAdvertV2AdvertNm extends Model
{
    protected $table = 'wb_api_advert_v2_advert_nms';

    protected $fillable = [
        'wb_api_advert_v2_advert_id',
        'bids_kopecks_search',
        'bids_kopecks_recommendations',
        'nm_id',
        'subject_id',
        'subject_name',
    ];

    public function wbApiAdvertV2Advert(): BelongsTo
    {
        return $this->belongsTo(WbApiAdvertV2Advert::class);
    }
}
