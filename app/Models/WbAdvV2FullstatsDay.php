<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbAdvV2FullstatsDay extends Model
{
    protected $table = 'wb_adv_fs_days';

    protected $fillable = [
        'wb_adv_v2_fullstats_wb_advert_id',
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
        'sum_price'
    ];

    public function wbAdvV2FullstatsWbAdvert()
    {
        return $this->belongsTo(WbAdvV2FullstatsWbAdvert::class);
    }

    public function wbAdvV2FullstatsApps()
    {
        return $this->hasMany(WbAdvV2FullstatsApp::class, 'wb_adv_fs_day_id');
    }
}
