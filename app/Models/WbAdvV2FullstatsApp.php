<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbAdvV2FullstatsApp extends Model
{
    protected $table = 'wb_adv_fs_apps';

    protected $fillable = [
        'wb_adv_fs_day_id',
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
        'app_type'
    ];

    public function wbAdvV2FullstatsDay()
    {
        return $this->belongsTo(WbAdvV2FullstatsDay::class, 'wb_adv_fs_day_id');
    }

    public function wbAdvV2FullstatsProducts()
    {
        return $this->hasMany(WbAdvV2FullstatsProduct::class, 'wb_adv_fs_app_id');
    }
}
