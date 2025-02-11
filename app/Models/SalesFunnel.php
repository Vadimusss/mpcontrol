<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'advertising_costs',
        'price_with_disc',
        'finished_price',
    ];

    protected $hidden = [
        'laravel_through_key'
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }
}
