<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationSetting extends Model
{
    protected $table = 'notification_settings';

    protected $fillable = [
        'module',
        'event',
        'channel',
        'is_enabled',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get all modules configuration
     */
    public static function getModulesConfig()
    {
        return [
            'users' => [
                'label' => self::translateLabel('admin_pages.notification_settings.modules.users'),
                'icon' => 'fas fa-users',
                'events' => [
                    'user_created' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.user_created'),
                        'channels' => ['email'],
                    ],
                    'user_updated' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.user_updated'),
                        'channels' => ['email'],
                    ],
                    'user_activated' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.user_activated'),
                        'channels' => ['email'],
                    ],
                    'role_assigned' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.role_assigned'),
                        'channels' => ['email'],
                    ],
                ],
            ],
            'courses' => [
                'label' => self::translateLabel('admin_pages.notification_settings.modules.courses'),
                'icon' => 'fas fa-graduation-cap',
                'events' => [
                    'course_created' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.course_created'),
                        'channels' => ['email'],
                    ],
                    'course_published' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.course_published'),
                        'channels' => ['email'],
                    ],
                    'course_expired' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.course_expired'),
                        'channels' => ['email'],
                    ],
                    'course_enrollment' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.course_enrollment'),
                        'channels' => ['email'],
                    ],
                    'course_due_reminder' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.course_due_reminder'),
                        'channels' => ['email'],
                    ],
                ],
            ],
            'lessons' => [
                'label' => self::translateLabel('admin_pages.notification_settings.modules.lessons'),
                'icon' => 'fas fa-file-alt',
                'events' => [
                    'lesson_added' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.lesson_added'),
                        'channels' => ['email'],
                    ],
                    'lesson_updated' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.lesson_updated'),
                        'channels' => ['email'],
                    ],
                ],
            ],
            'assessments' => [
                'label' => self::translateLabel('admin_pages.notification_settings.modules.assessments'),
                'icon' => 'fas fa-clipboard-check',
                'events' => [
                    'test_assigned' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.test_assigned'),
                        'channels' => ['email'],
                    ],
                    'test_completed' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.test_completed'),
                        'channels' => ['email'],
                    ],
                    'test_results_published' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.test_results_published'),
                        'channels' => ['email'],
                    ],
                ],
            ],
            'trainees' => [
                'label' => self::translateLabel('admin_pages.notification_settings.modules.trainees'),
                'icon' => 'fas fa-user-graduate',
                'events' => [
                    'trainee_enrolled' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.trainee_enrolled'),
                        'channels' => ['email'],
                    ],
                    'trainee_completed_course' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.trainee_completed_course'),
                        'channels' => ['email'],
                    ],
                ],
            ],
            'trainers' => [
                'label' => self::translateLabel('admin_pages.notification_settings.modules.trainers'),
                'icon' => 'fas fa-chalkboard-user',
                'events' => [
                    'trainer_assigned' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.trainer_assigned'),
                        'channels' => ['email'],
                    ],
                ],
            ],
            'system' => [
                'label' => self::translateLabel('admin_pages.notification_settings.modules.system'),
                'icon' => 'fas fa-shield-alt',
                'events' => [
                    'login_alerts' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.login_alerts'),
                        'channels' => ['email'],
                    ],
                    'password_reset' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.password_reset'),
                        'channels' => ['email'],
                    ],
                    'failed_login' => [
                        'label' => self::translateLabel('admin_pages.notification_settings.events.failed_login'),
                        'channels' => ['email'],
                    ],
                ],
            ],
        ];
    }

    protected static function translateLabel(string $key): string
    {
        $translated = __($key);
        if ($translated !== $key) {
            return $translated;
        }

        $fallbackLocale = (string) config('app.fallback_locale', 'en');
        $fallback = trans($key, [], $fallbackLocale);
        if ($fallback !== $key) {
            return $fallback;
        }

        return Str::headline((string) last(explode('.', $key)));
    }

    /**
     * Relationship with audit logs
     */
    public function auditLogs()
    {
        return $this->hasMany(NotificationSettingsAuditLog::class);
    }

    /**
     * Scope to filter by module
     */
    public function scopeModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to filter by channel
     */
    public function scopeChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to filter enabled only
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
