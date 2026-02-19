<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalNsi extends Model
{
    protected $fillable = [
        'good_id',
        'cabinet',
        'article_wb',
        'sku_wb',
        'article_oz',
        'sku_oz',
        'product_name',
        'fg_0',
        'fg_1',
        'fg_2',
        'fg_3',
        'brand',
        'subject',
        'category_oz',
        'barcode',
        'cost_price',
    ];

    protected $casts = [
        'sku_wb' => 'integer',
        'sku_oz' => 'integer',
        'barcode' => 'integer',
        'cost_price' => 'decimal:2',
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }
}