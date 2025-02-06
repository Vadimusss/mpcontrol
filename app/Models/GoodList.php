<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GoodList extends Model
{
    protected $fillable = [
        'shop_id',
        'user_id',
        'name'
    ];

    protected $with = ['creator'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function goods(): BelongsToMany
    {
        return $this->belongsToMany(Good::class);
    }

    public function connectedWorkSpaces(): BelongsToMany
    {
        return $this->belongsToMany(WorkSpace::class);
    }

    public function connectedReports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class);
    }
}