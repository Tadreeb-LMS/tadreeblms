<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    public const MEETING_PROVIDERS = ['zoom', 'teams', 'google_meet'];

    protected $fillable = [
        'course_id',
        'provider',
        'meeting_provider',
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

    public static function normalizeMeetingProvider(?string $provider): ?string
    {
        return match ($provider) {
            'zoom' => 'zoom',
            'teams' => 'teams',
            'google_meet', 'google-meet-integration' => 'google_meet',
            default => null,
        };
    }

    public function setMeetingProviderAttribute(?string $provider): void
    {
        $this->attributes['meeting_provider'] = self::normalizeMeetingProvider($provider);
    }

    public function getMeetingProviderAttribute($provider): ?string
    {
        return self::normalizeMeetingProvider($provider ?? ($this->attributes['provider'] ?? null));
    }
}
