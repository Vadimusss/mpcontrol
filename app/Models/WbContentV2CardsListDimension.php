<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbContentV2CardsListDimension extends Model
{
    protected $table = 'wb_cards_dimensions';

    protected $fillable = [
        'wb_cards_list_id',
        'width',
        'height',
        'length',
        'weight_brutto',
        'is_valid',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
    ];

    public function cardsList(): BelongsTo
    {
        return $this->belongsTo(WbContentV2CardsList::class, 'wb_cards_list_id');
    }
}
