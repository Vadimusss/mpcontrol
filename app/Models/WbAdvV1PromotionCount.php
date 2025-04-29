<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbAdvV1PromotionCount extends Model
{
    protected $fillable = [
        'shop_id',
        'type', 
        'status',
        'advert_id',
        'change_time'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
