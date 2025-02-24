<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    protected $fillable = [
        'key',
        'shop_id',
        'is_active',
        'expires_at'
    ];

    protected $attributes = [
        'is_busy' => false,
        'is_active' => true
    ];

    // protected $with = ['is_active', 'updated_at'];

    public function connectedShop(): BelongsTo
    {
        return $this->BelongsTo(Shop::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
