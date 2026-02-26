<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbExpensesByOrderDay extends Model
{
    protected $table = 'wb_expenses_by_order_days';

    protected $fillable = [
        'shop_id',
        'order_date',
        'nm_id',
        'orders_count',
        'op_after_spp',
        'logistics_total',
        'amount_to_transfer',
    ];

    protected $casts = [
        'order_date' => 'date',
        'op_after_spp' => 'decimal:2',
        'logistics_total' => 'decimal:2',
        'amount_to_transfer' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
