<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbContentV2CardsListSize extends Model
{
    protected $table = 'wb_cards_sizes';

    protected $fillable = [
        'wb_cards_list_id',
        'chrt_id',
        'tech_size',
        'wb_size',
        'price',
        'skus_text',
    ];

    public function cardsList(): BelongsTo
    {
        return $this->belongsTo(WbContentV2CardsList::class, 'wb_cards_list_id');
    }
}
