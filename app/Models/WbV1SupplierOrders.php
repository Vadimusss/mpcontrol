<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class WbV1SupplierOrders extends Model
{
    protected $fillable = [
        'shop_id',
        'date',
        'last_change_date',
        'warehouse_name',
        'warehouse_type',
        'country_name',
        'oblast_okrug_name',
        'region_name',
        'supplier_article',
        'nm_id',
        'barcode',
        'category',
        'subject',
        'brand',
        'tech_size',
        'income_id',
        'is_supply',
        'is_realization',
        'total_price',
        'discount_percent',
        'spp',
        'finished_price',
        'price_with_disc',
        'is_cancel',
        'cancel_date',
        'order_type',
        'sticker',
        'g_number',
        'srid',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}