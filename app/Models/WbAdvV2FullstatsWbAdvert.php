<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbAdvV2FullstatsWbAdvert extends Model
{
    protected $fillable = [
        'shop_id',
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
        'date',
        'advert_id'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function wbAdvV2FullstatsDays()
    {
        return $this->hasMany(WbAdvV2FullstatsDay::class);
    }
}
