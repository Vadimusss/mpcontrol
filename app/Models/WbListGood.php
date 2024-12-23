<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbListGood extends Model
{
    protected $fillable = [
        'good_id',
        'nm_id',
        'vendor_code',
        'currency_iso_code_4217',
        'discount',
        'club_discount',
        'editable_size_price',
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(WbListGoodSize::class);
    }
}
