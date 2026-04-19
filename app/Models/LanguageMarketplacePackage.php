<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageMarketplacePackage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'github_synced_at' => 'datetime',
    ];
}
