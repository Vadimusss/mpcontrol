<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WbAdvV0AuctionAdvert extends Model
{
    protected $table = 'wb_adv_v0_auction_adverts';

    protected $fillable = [
        'shop_id',
        'advert_id',
        'bid_type',
        'bids_recommendations',
        'bids_search',
        'nm_id',
        'subject_id',
        'subject_name',
        'name',
        'payment_type',
        'placements_recommendations',
        'placements_search',
        'status',
        'created',
        'deleted',
        'started',
        'updated',
    ];

    protected $casts = [
        'created' => 'datetime',
        'deleted' => 'datetime',
        'started' => 'datetime',
        'updated' => 'datetime',
        'placements_recommendations' => 'boolean',
        'placements_search' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
