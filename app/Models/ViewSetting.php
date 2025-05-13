<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewSetting extends Model
{
    protected $fillable = [
        'work_space_id',
        'view_id',
        'settings',
    ];

    protected $with = ['view'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(WorkSpace::class);
    }

    public function view(): BelongsTo
    {
        return $this->belongsTo(View::class);
    }
}
