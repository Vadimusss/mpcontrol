<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WbApiAdvertV2Advert extends Model
{
    protected $table = 'wb_api_advert_v2_adverts';

    protected $fillable = [
        'shop_id',
        'advert_id',
        'bid_type',
        'status',
        'payment_type',
        'settings_name',
        'settings_payment_type',
        'placements_search',
        'placements_recommendations',
        'timestamps_created',
        'timestamps_updated',
        'timestamps_started',
        'timestamps_deleted',
    ];

    protected $casts = [
        'placements_search' => 'boolean',
        'placements_recommendations' => 'boolean',
        'timestamps_created' => 'datetime',
        'timestamps_updated' => 'datetime',
        'timestamps_started' => 'datetime',
        'timestamps_deleted' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function nmSettings(): HasMany
    {
        return $this->hasMany(WbApiAdvertV2AdvertNm::class);
    }
}
