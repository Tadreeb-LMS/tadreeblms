<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_key',
        'status',
        'max_users',
        'license_type',
        'licensed_to',
        'licensee_email',
        'expiry_date',
        'support_valid_until',
        'last_validated_at',
        'validation_response',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'support_valid_until' => 'datetime',
        'last_validated_at' => 'datetime',
        'validation_response' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'max_users' => 'integer',
    ];

    /**
     * Encrypt the license key when setting.
     */
    public function setLicenseKeyAttribute($value)
    {
        $this->attributes['license_key'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt the license key when getting.
     */
    public function getLicenseKeyAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Get the masked license key for display.
     */
    public function getMaskedKeyAttribute()
    {
        $key = $this->license_key;
        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }
        return substr($key, 0, 4) . str_repeat('*', strlen($key) - 8) . substr($key, -4);
    }

    /**
     * Check if the license is valid (active and not expired).
     */
    public function isValid()
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the license needs revalidation.
     */
    public function needsRevalidation()
    {
        if (!$this->last_validated_at) {
            return true;
        }

        $interval = config('keygen.revalidation_interval', 24);
        return $this->last_validated_at->addHours($interval)->isPast();
    }

    /**
     * Check if within grace period (for when Keygen.sh is unreachable).
     */
    public function isWithinGracePeriod()
    {
        if (!$this->last_validated_at) {
            return false;
        }

        $gracePeriod = config('keygen.grace_period', 7);
        return !$this->last_validated_at->addDays($gracePeriod)->isPast();
    }

    /**
     * Get the currently active license.
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'active' => 'success',
            'expired' => 'warning',
            'revoked' => 'danger',
            'invalid' => 'danger',
            'pending' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get status label for UI.
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }
}