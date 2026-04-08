<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'is_enabled' => 'boolean',
        'library_uploaded_at' => 'datetime',
    ];
}
