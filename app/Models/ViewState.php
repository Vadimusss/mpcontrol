<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewState extends Model
{
    protected $fillable = ['user_id', 'workspace_id', 'view_id', 'view_state'];
    
    protected $casts = [
        'view_state' => 'array',
    ];

    protected $attributes = [
        'view_state' => '{}' 
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function workspace()
    {
        return $this->belongsTo(WorkSpace::class);
    }
    
    public function view()
    {
        return $this->belongsTo(View::class);
    }
}