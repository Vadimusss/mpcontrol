<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbAdvV1Upd extends Model
{
    protected $fillable = [
        'good_id',
        'upd_num',
        'upd_time',
        'upd_sum',
        'advert_id',
        'camp_name',
        'advert_type',
        'payment_type',
        'advert_status',
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }
}
