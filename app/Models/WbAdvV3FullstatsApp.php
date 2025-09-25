<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WbAdvV3FullstatsApp extends Model
{
    protected $table = 'wb_adv_v3_fs_apps';

    protected $fillable = [
        'wb_adv_v3_fs_day_id',
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
        'canceled',
        'app_type'
    ];

    public function wbAdvV3FullstatsDay(): BelongsTo
    {
        return $this->belongsTo(WbAdvV3FullstatsDay::class, 'wb_adv_v3_fs_day_id');
    }

    public function wbAdvV3FullstatsProducts(): HasMany
    {
        return $this->hasMany(WbAdvV3FullstatsProduct::class, 'wb_adv_v3_fs_app_id');
    }
}
