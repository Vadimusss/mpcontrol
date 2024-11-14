<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shop extends Model
{
    protected $fillable = [
        'api_key_id',
        'name',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function apiKey(): HasOne
    {
        return $this->HasOne(ApiKey::class);
    }
}
