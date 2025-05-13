<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class View extends Model
{
    protected $fillable = [
        'name',
        'pageName',
    ];

    public function workspaces(): HasMany
    {
        return $this->hasMany(WorkSpace::class);
    }

    public function viewSettings(): HasMany
    {
        return $this->hasMany(ViewSetting::class);
    }
}
