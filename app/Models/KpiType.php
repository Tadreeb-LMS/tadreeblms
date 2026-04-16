<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiType extends Model
{
    protected $fillable = [
        'key',
        'label',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
