<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierWarehousesStocks extends Model
{
    protected $fillable = [
        'shop_id',
        'date',
        'office_id',
        'warehouse_name',
        'warehouse_id',
        'nm_id',
        'vendor_code',
        'barcode',
        'amount',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
