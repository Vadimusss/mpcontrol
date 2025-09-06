<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbContentV2CardsListPhoto extends Model
{
    protected $table = 'wb_cards_photos';

    protected $fillable = [
        'wb_cards_list_id',
        'big',
        'c246x328',
        'c516x688',
        'square',
        'tm',
    ];

    public function cardsList(): BelongsTo
    {
        return $this->belongsTo(WbContentV2CardsList::class, 'wb_cards_list_id');
    }
}
