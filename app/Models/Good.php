<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Good extends Model
{
    protected $fillable = [
        'shop_id',
        'nm_id',
        'vendor_code',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function lists(): BelongsToMany
    {
        return $this->hasMany(GoodList::class);
    }

    public function wbListGoodRow(): HasOne
    {
        return $this->HasOne(WbListGood::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(WbListGoodSize::class);
    }
}
