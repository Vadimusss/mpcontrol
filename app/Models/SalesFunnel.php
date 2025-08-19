<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesFunnel extends Model
{
    protected $fillable = [
        'good_id',
        'vendor_code',
        'nm_id',
        'imt_name',
        'date',
        'open_card_count',
        'add_to_cart_count',
        'orders_count',
        'orders_sum_rub',
        'buyouts_count',
        'buyouts_sum_rub',
        'buyout_percent',
        'advertising_costs',
        'price_with_disc',
        'finished_price',
        'aac_cpm',
        'aac_views',
        'aac_clicks',
        'aac_orders',
        'aac_sum',
        'auc_cpm',
        'auc_views',
        'auc_clicks',
        'auc_orders',
        'auc_sum',
    ];

    protected $hidden = [
        'laravel_through_key'
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }
}
