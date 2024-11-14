<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    protected $fillable = [
        'key',
        'shop_id'
    ];

    protected $attributes = [
        'is_busy' => false,
    ];

    public function connectedShop(): BelongsTo
    {
        return $this->BelongsTo(Shop::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
