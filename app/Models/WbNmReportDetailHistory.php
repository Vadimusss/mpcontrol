<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbNmReportDetailHistory extends Model
{
    protected $fillable = [
        'good_id',
        'nm_id',
        'imt_name',
        'vendor_code',
        'dt',
        'open_card_count',
        'add_to_cart_count',
        'orders_count',
        'orders_sum_rub',
        'buyouts_count',
        'buyouts_sum_rub',
        'buyout_percent',
        'add_to_cart_conversion',
        'cart_to_order_conversion',
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }
}
