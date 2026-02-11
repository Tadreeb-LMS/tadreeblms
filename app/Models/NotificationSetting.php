<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
                'label' => 'User Management',
                'icon' => 'fas fa-users',
                'events' => [
                    'user_created' => [
                        'label' => 'User Created',
                        'channels' => ['email'],
                    ],
                    'user_updated' => [
                        'label' => 'User Updated',
                        'channels' => ['email'],
                    ],
                    'user_activated' => [
                        'label' => 'User Activated / Deactivated',
                        'channels' => ['email'],
                    ],
                    'role_assigned' => [
                        'label' => 'Role Assigned',
                        'channels' => ['email'],
                    ],
                ],
            ],
            'courses' => [
                'label' => 'Courses',
                'icon' => 'fas fa-graduation-cap',
                'events' => [
                    'course_created' => [
                        'label' => 'Course Created',
                        'channels' => ['email'],
                    ],
                    'course_published' => [
                        'label' => 'Course Published / Unpublished',
                        'channels' => ['email'],
                    ],
                    'course_expired' => [
                        'label' => 'Course Expired',
                        'channels' => ['email'],
                    ],
                    'course_enrollment' => [
                        'label' => 'Course Enrollment',
                        'channels' => ['email'],
                    ],
                    'course_due_reminder' => [
                        'label' => 'Course Due Reminder',
                        'channels' => ['email'],
                    ],
                ],
            ],
            'lessons' => [
                'label' => 'Lessons',
                'icon' => 'fas fa-file-alt',
                'events' => [
                    'lesson_added' => [
                        'label' => 'Lesson Added',
                        'channels' => ['email'],
                    ],
                    'lesson_updated' => [
                        'label' => 'Lesson Updated',
                        'channels' => ['email'],
                    ],
                ],
            ],
            'assessments' => [
                'label' => 'Assessments / Tests',
                'icon' => 'fas fa-clipboard-check',
                'events' => [
                    'test_assigned' => [
                        'label' => 'Test Assigned',
                        'channels' => ['email'],
                    ],
                    'test_completed' => [
                        'label' => 'Test Completed',
                        'channels' => ['email'],
                    ],
                    'test_results_published' => [
                        'label' => 'Test Results Published',
                        'channels' => ['email'],
                    ],
                ],
            ],
            'trainees' => [
                'label' => 'Trainees',
                'icon' => 'fas fa-user-graduate',
                'events' => [
                    'trainee_enrolled' => [
                        'label' => 'Trainee Enrolled',
                        'channels' => ['email'],
                    ],
                    'trainee_completed_course' => [
                        'label' => 'Trainee Completed Course',
                        'channels' => ['email'],
                    ],
                ],
            ],
            'trainers' => [
                'label' => 'Trainers',
                'icon' => 'fas fa-chalkboard-teacher',
                'events' => [
                    'trainer_assigned' => [
                        'label' => 'Trainer Assigned to Course',
                        'channels' => ['email'],
                    ],
                ],
            ],
            'system' => [
                'label' => 'System / Security',
                'icon' => 'fas fa-shield-alt',
                'events' => [
                    'login_alerts' => [
                        'label' => 'Login Alerts',
                        'channels' => ['email'],
                    ],
                    'password_reset' => [
                        'label' => 'Password Reset',
                        'channels' => ['email'],
                    ],
                    'failed_login' => [
                        'label' => 'Failed Login Attempts',
                        'channels' => ['email'],
                    ],
                ],
            ],
        ];
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
