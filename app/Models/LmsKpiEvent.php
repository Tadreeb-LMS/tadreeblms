<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class LmsKpiEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event_type',
        'occurred_at',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'created_at' => 'datetime',
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
