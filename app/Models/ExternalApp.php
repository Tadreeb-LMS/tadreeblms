<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_enabled',
        'is_setup',
        'version',
        'installed_path',
        'config_file',
        'configuration',
        'status',
        'error_message',
        'installed_at',
        'last_updated_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_setup' => 'boolean',
        'configuration' => 'array',
        'installed_at' => 'datetime',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Scope to get only enabled external apps
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope to get only active external apps
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if app is properly installed and configured
     */
    public function isInstalled(): bool
    {
        return $this->installed_path && $this->status === 'active';
    }

    /**
     * Get the readable status
     */
    public function getStatusBadge()
    {
        $badges = [
            'active' => 'success',
            'inactive' => 'secondary',
            'error' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }
}
