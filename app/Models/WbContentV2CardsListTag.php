<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbContentV2CardsListTag extends Model
{
    protected $table = 'wb_cards_tags';

    protected $fillable = [
        'wb_cards_list_id',
        'tag_id',
        'name',
        'color',
    ];

    public function cardsList(): BelongsTo
    {
        return $this->belongsTo(WbContentV2CardsList::class, 'wb_cards_list_id');
    }
}
