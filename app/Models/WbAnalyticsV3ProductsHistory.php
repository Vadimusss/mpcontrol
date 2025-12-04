<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbAnalyticsV3ProductsHistory extends Model // /api/analytics/v3/sales-funnel/products/history
{
    protected $fillable = [
        'good_id',
        'nm_id',
        'title',
        'vendor_code',
        'brand_name',
        'subject_id',
        'subject_name',
        'date',
        'open_count',
        'cart_count',
        'order_count',
        'order_sum',
        'buyout_count',
        'buyout_sum',
        'buyout_percent',
        'add_to_cart_conversion',
        'cart_to_order_conversion',
        'add_to_wishlist_count',
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }
}
