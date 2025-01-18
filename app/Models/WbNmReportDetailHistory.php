<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbNmReportDetailHistory extends Model
{
    protected $fillable = [
        'good_id',
        'nm_id',
        'imtName',
        'vendor_code',
        'dt',
        'openCardCount',
        'addToCartCount',
        'ordersCount',
        'ordersSumRub',
        'buyoutsCount',
        'buyoutsSumRub',
        'buyoutPercent',
        'addToCartConversion',
        'cartToOrderConversion',
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }
}
