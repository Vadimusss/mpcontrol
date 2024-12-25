<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Shop extends Model
{
    protected $fillable = [
        'api_key_id',
        'name',
    ];

    protected $with = ['owner', 'customers'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function apiKey(): HasOne
    {
        return $this->HasOne(ApiKey::class);
    }

    public function workSpaces(): HasMany
    {
        return $this->hasMany(WorkSpace::class);
    }

    public function goodLists(): HasMany
    {
        return $this->hasMany(GoodList::class);
    }

    public function goods(): HasMany
    {
        return $this->hasMany(Good::class);
    }

    public function sizes(): HasManyThrough
    {
        return $this->hasManyThrough(WbListGoodSize::class, Good::class);
    }
}
