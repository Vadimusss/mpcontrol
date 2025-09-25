<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WbAdvV3FullstatsDay extends Model
{
    protected $table = 'wb_adv_v3_fs_days';

    protected $fillable = [
        'wb_adv_v3_fullstats_wb_advert_id',
        'date',
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
        'canceled'
    ];

    public function wbAdvV3FullstatsWbAdvert(): BelongsTo
    {
        return $this->belongsTo(WbAdvV3FullstatsWbAdvert::class);
    }

    public function wbAdvV3FullstatsApps(): HasMany
    {
        return $this->hasMany(WbAdvV3FullstatsApp::class, 'wb_adv_v3_fs_day_id');
    }
}
