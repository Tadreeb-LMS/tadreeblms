<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class LiveSessionAttendance extends Model
{
    protected $fillable = [
        'live_session_id',
        'user_id',
        'attended_at',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    public function liveSession()
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
