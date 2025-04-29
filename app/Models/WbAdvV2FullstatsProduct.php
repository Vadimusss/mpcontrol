<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbAdvV2FullstatsProduct extends Model
{
    protected $table = 'wb_adv_fs_products';

    protected $fillable = [
        'wb_adv_fs_app_id',
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
        'name',
        'nm_id'
    ];

    public function wbAdvV2FullstatsApp()
    {
        return $this->belongsTo(WbAdvV2FullstatsApp::class, 'wb_adv_fs_app_id');
    }

    public function good()
    {
        return $this->belongsTo(Good::class);
    }
}
