<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkSpace extends Model
{
    protected $fillable = [
        'shop_id',
        'user_id',
        'name',
    ];

    protected $with = ['creator', 'connectedGoodLists', 'viewSettings'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function connectedGoodLists(): BelongsToMany
    {
        return $this->belongsToMany(GoodList::class);
    }

    public function viewSettings(): HasOne
    {
        return $this->hasOne(ViewSetting::class);
    }

    public function viewStates(): HasMany
    {
        return $this->hasMany(ViewState::class, 'workspace_id');
    }
}
