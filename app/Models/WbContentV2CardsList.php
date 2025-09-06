<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WbContentV2CardsList extends Model
{
    protected $fillable = [
        'shop_id',
        'nm_id',
        'imt_id',
        'nm_uuid',
        'subject_id',
        'subject_name',
        'vendor_code',
        'brand',
        'title',
        'description',
        'need_kiz',
        'video',
        'wholesale_enabled',
        'wholesale_quantum',
        'created_at_api',
        'updated_at_api',
    ];

    protected $casts = [
        'need_kiz' => 'boolean',
        'wholesale_enabled' => 'boolean',
        'created_at_api' => 'datetime',
        'updated_at_api' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(WbContentV2CardsListPhoto::class);
    }

    public function characteristics(): HasMany
    {
        return $this->hasMany(WbContentV2CardsListCharacteristic::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(WbContentV2CardsListSize::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(WbContentV2CardsListTag::class);
    }

    public function dimensions(): HasMany
    {
        return $this->hasMany(WbContentV2CardsListDimension::class);
    }
}
