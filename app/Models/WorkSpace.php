<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkSpace extends Model
{
    protected $fillable = [
        'shop_id',
        'user_id',
        'name'
    ];

    protected $with = ['creator', 'connectedGoodLists'];

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
}
