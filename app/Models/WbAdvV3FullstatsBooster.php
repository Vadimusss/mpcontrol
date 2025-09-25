<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbAdvV3FullstatsBooster extends Model
{
    protected $table = 'wb_adv_v3_fs_boosters';

    protected $fillable = [
        'wb_adv_v3_fullstats_wb_advert_id',
        'avg_position',
        'date',
        'nm_id'
    ];

    public function wbAdvV3FullstatsWbAdvert(): BelongsTo
    {
        return $this->belongsTo(WbAdvV3FullstatsWbAdvert::class);
    }
}
