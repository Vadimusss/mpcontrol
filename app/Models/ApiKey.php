<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiKey extends Model
{
    public function ownShops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }
}
