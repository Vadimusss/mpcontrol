<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbContentV2CardsListCharacteristic extends Model
{
    protected $table = 'wb_cards_characteristics';

    protected $fillable = [
        'wb_cards_list_id',
        'characteristic_id',
        'name',
        'values_text',
    ];

    public function cardsList(): BelongsTo
    {
        return $this->belongsTo(WbContentV2CardsList::class, 'wb_cards_list_id');
    }
}
