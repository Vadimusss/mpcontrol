<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbListGoodSize extends Model
{
    protected $fillable = [
        'good_id',
        'size_id',
        'price',
        'discounted_price',
        'club_discounted_price',
        'tech_size_name',
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(WbListGood::class);
    }
}
