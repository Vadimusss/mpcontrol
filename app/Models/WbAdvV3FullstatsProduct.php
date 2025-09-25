<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbAdvV3FullstatsProduct extends Model
{
    protected $table = 'wb_adv_v3_fs_products';

    protected $fillable = [
        'wb_adv_v3_fs_app_id',
        'good_id',
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
        'canceled',
        'name',
        'nm_id'
    ];

    public function wbAdvV3FullstatsApp(): BelongsTo
    {
        return $this->belongsTo(WbAdvV3FullstatsApp::class, 'wb_adv_v3_fs_app_id');
    }

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }
}
