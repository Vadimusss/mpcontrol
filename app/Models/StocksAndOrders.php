<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StocksAndOrders extends Model
{
    protected $fillable = [
        'shop_id',
        'barcode',
        'supplier_article',
        'nm_id',
        'warehouse_name',
        'date',
        'quantity',
        'orders_count',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
