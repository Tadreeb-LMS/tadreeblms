<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSettingsAuditLog extends Model
{
    protected $table = 'notification_settings_audit_log';

    protected $fillable = [
        'notification_setting_id',
        'user_id',
        'action',
        'module',
        'event',
        'channel',
        'old_value',
        'new_value',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'old_value' => 'boolean',
        'new_value' => 'boolean',
        'changes' => 'array',
    ];

    /**
     * Relationship with notification setting
     */
    public function notificationSetting()
    {
        return $this->belongsTo(NotificationSetting::class);
    }

    /**
     * Relationship with user
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\Auth\User::class);
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'enabled' => 'Enabled',
            'disabled' => 'Disabled',
            'bulk_module_enabled' => 'Bulk Module Enabled',
            'bulk_module_disabled' => 'Bulk Module Disabled',
            'bulk_channel_enabled' => 'Bulk Channel Enabled',
            'bulk_channel_disabled' => 'Bulk Channel Disabled',
        ];

        return $labels[$this->action] ?? ucfirst($this->action);
    }
}
