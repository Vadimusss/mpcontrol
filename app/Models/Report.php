<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Report extends Model
{
    protected $fillable = [
        'shop_id',
        'type_id',
    ];

    protected $with = ['type','connectedGoodLists'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function connectedGoodLists(): BelongsToMany
    {
        return $this->belongsToMany(GoodList::class);
    }
}
