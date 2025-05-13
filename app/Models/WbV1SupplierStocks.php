<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbV1SupplierStocks extends Model
{
    protected $fillable = [
        'shop_id',
        'last_change_date',
        'warehouse_name',
        'supplier_article',
        'nm_id',
        'barcode',
        'quantity',
        'in_way_to_client',
        'in_way_from_client',
        'quantity_full',
        'category',
        'subject',
        'brand',
        'tech_size',
        'price',
        'discount',
        'is_supply',
        'is_realization',
        's_c_code',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
