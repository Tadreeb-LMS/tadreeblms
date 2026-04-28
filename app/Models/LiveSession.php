<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    protected $fillable = [
        'course_id',
        'provider',
        'session_date',
        'session_time',
        'meeting_link',
        'meeting_id',
        'host_url',
        'duration',
        'recurrence_type',
        'created_by',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances()
    {
        return $this->hasMany(LiveSessionAttendance::class);
    }
}
