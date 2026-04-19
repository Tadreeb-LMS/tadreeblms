<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageTranslationInvitation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'viewed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];
}
