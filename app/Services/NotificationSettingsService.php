<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Models\NotificationSettingsAuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NotificationSettingsService
{
    const CACHE_KEY = 'notification_settings';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Check if a notification should be sent
     */
    public function shouldNotify($module, $event, $channel)
    {
        $settings = $this->getAllSettings();
        $key = "{$module}.{$event}.{$channel}";

        return $settings[$key] ?? true; // Default to enabled if not found
    }

    /**
     * Get enabled channels for a specific module/event
     */
    public function getEnabledChannels($module, $event)
    {
        $settings = $this->getAllSettings();
        $channels = [];

        $config = NotificationSetting::getModulesConfig();
        $availableChannels = $config[$module]['events'][$event]['channels'] ?? ['email'];

        foreach ($availableChannels as $channel) {
            $key = "{$module}.{$event}.{$channel}";
            if ($settings[$key] ?? true) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }

    /**
     * Get all settings from cache or database
     */
    public function getAllSettings()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $settings = NotificationSetting::all();
            $result = [];

            foreach ($settings as $setting) {
                $key = "{$setting->module}.{$setting->event}.{$setting->channel}";
                $result[$key] = $setting->is_enabled;
            }

            return $result;
        });
    }

    /**
     * Get all settings as a structured array for the UI
     */
    public function getSettingsForUI()
    {
        $dbSettings = NotificationSetting::all()->keyBy(function ($item) {
            return "{$item->module}.{$item->event}.{$item->channel}";
        });

        $config = NotificationSetting::getModulesConfig();
        $result = [];

        foreach ($config as $moduleKey => $module) {
            $result[$moduleKey] = [
                'label' => $module['label'],
                'icon' => $module['icon'],
                'events' => [],
            ];

            foreach ($module['events'] as $eventKey => $event) {
                $result[$moduleKey]['events'][$eventKey] = [
                    'label' => $event['label'],
                    'channels' => [],
                ];

                foreach ($event['channels'] as $channel) {
                    $key = "{$moduleKey}.{$eventKey}.{$channel}";
                    $dbSetting = $dbSettings->get($key);

                    $result[$moduleKey]['events'][$eventKey]['channels'][$channel] = [
                        'enabled' => $dbSetting ? $dbSetting->is_enabled : true,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Update a single setting
     */
    public function updateSetting($module, $event, $channel, $enabled, $userId, $ipAddress = null)
    {
        return DB::transaction(function () use ($module, $event, $channel, $enabled, $userId, $ipAddress) {
            $setting = NotificationSetting::firstOrNew([
                'module' => $module,
                'event' => $event,
                'channel' => $channel,
            ]);

            $oldValue = $setting->exists ? $setting->is_enabled : true;
            $setting->is_enabled = $enabled;
            $setting->save();

            // Log the change
            NotificationSettingsAuditLog::create([
                'notification_setting_id' => $setting->id,
                'user_id' => $userId,
                'action' => $enabled ? 'enabled' : 'disabled',
                'module' => $module,
                'event' => $event,
                'channel' => $channel,
                'old_value' => $oldValue,
                'new_value' => $enabled,
                'ip_address' => $ipAddress,
            ]);

            $this->clearCache();

            return $setting;
        });
    }

    /**
     * Bulk update all settings for a module
     */
    public function bulkUpdateModule($module, $enabled, $userId, $ipAddress = null)
    {
        return DB::transaction(function () use ($module, $enabled, $userId, $ipAddress) {
            $config = NotificationSetting::getModulesConfig();
            $moduleConfig = $config[$module] ?? null;

            if (!$moduleConfig) {
                return false;
            }

            $changes = [];

            foreach ($moduleConfig['events'] as $eventKey => $event) {
                foreach ($event['channels'] as $channel) {
                    $setting = NotificationSetting::firstOrNew([
                        'module' => $module,
                        'event' => $eventKey,
                        'channel' => $channel,
                    ]);

                    $oldValue = $setting->exists ? $setting->is_enabled : true;
                    $setting->is_enabled = $enabled;
                    $setting->save();

                    $changes[] = [
                        'event' => $eventKey,
                        'channel' => $channel,
                        'old_value' => $oldValue,
                        'new_value' => $enabled,
                    ];
                }
            }

            // Log the bulk change
            NotificationSettingsAuditLog::create([
                'user_id' => $userId,
                'action' => $enabled ? 'bulk_module_enabled' : 'bulk_module_disabled',
                'module' => $module,
                'changes' => $changes,
                'ip_address' => $ipAddress,
            ]);

            $this->clearCache();

            return true;
        });
    }

    /**
     * Bulk update all settings for a channel
     */
    public function bulkUpdateChannel($channel, $enabled, $userId, $ipAddress = null)
    {
        return DB::transaction(function () use ($channel, $enabled, $userId, $ipAddress) {
            $config = NotificationSetting::getModulesConfig();
            $changes = [];

            foreach ($config as $moduleKey => $module) {
                foreach ($module['events'] as $eventKey => $event) {
                    if (!in_array($channel, $event['channels'])) {
                        continue;
                    }

                    $setting = NotificationSetting::firstOrNew([
                        'module' => $moduleKey,
                        'event' => $eventKey,
                        'channel' => $channel,
                    ]);

                    $oldValue = $setting->exists ? $setting->is_enabled : true;
                    $setting->is_enabled = $enabled;
                    $setting->save();

                    $changes[] = [
                        'module' => $moduleKey,
                        'event' => $eventKey,
                        'old_value' => $oldValue,
                        'new_value' => $enabled,
                    ];
                }
            }

            // Log the bulk change
            NotificationSettingsAuditLog::create([
                'user_id' => $userId,
                'action' => $enabled ? 'bulk_channel_enabled' : 'bulk_channel_disabled',
                'channel' => $channel,
                'changes' => $changes,
                'ip_address' => $ipAddress,
            ]);

            $this->clearCache();

            return true;
        });
    }

    /**
     * Get audit log entries
     */
    public function getAuditLog($perPage = 20)
    {
        return NotificationSettingsAuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Clear the settings cache
     */
    public function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Seed default settings (all enabled)
     */
    public function seedDefaults()
    {
        $config = NotificationSetting::getModulesConfig();

        foreach ($config as $moduleKey => $module) {
            foreach ($module['events'] as $eventKey => $event) {
                foreach ($event['channels'] as $channel) {
                    NotificationSetting::firstOrCreate([
                        'module' => $moduleKey,
                        'event' => $eventKey,
                        'channel' => $channel,
                    ], [
                        'is_enabled' => true,
                    ]);
                }
            }
        }

        $this->clearCache();
    }
}
