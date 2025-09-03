<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbApiV3Warehouses extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'office_id',
        'wb_id',
        'cargo_type',
        'delivery_type',
        'is_deleting',
        'is_processing',
    ];

    protected $casts = [
        'is_deleting' => 'boolean',
        'is_processing' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
