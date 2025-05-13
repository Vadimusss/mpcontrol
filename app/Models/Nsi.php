<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nsi extends Model
{
    protected $fillable = [
        'good_id',
        'vendor_code',
        'name',
        'variant',
        'fg_0',
        'fg_1', 
        'fg_2',
        'fg_3',
        'set',
        'series',
        'status',
        'cost_with_taxes',
        'barcode',
        'nm_id',
        'wb_object',
        'wb_volume',
        'wb_1',
        'wb_2'
    ];

    protected $casts = [
        'cost_with_taxes' => 'float',
        'wb_volume' => 'float',
        'nm_id' => 'integer'
    ];

    public function good()
    {
        return $this->belongsTo(Good::class);
    }
}
