<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    protected $fillable = [
        'text',
        'date',
        'good_id',
        'view_id',
        'user_id'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    protected $with = ['creator'];

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }

    public function view(): BelongsTo
    {
        return $this->belongsTo(View::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
