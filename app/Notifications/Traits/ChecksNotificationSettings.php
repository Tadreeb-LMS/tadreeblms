<?php

namespace App\Notifications\Traits;

use App\Services\NotificationSettingsService;

trait ChecksNotificationSettings
{
    /**
     * Get enabled channels based on notification settings
     *
     * @param mixed $notifiable
     * @param string $module
     * @param string $event
     * @return array
     */
    protected function getEnabledChannels($notifiable, $module, $event)
    {
        $service = app(NotificationSettingsService::class);
        return $service->getEnabledChannels($module, $event);
    }

    /**
     * Check if a specific channel is enabled for a notification
     *
     * @param string $module
     * @param string $event
     * @param string $channel
     * @return bool
     */
    protected function isChannelEnabled($module, $event, $channel)
    {
        $service = app(NotificationSettingsService::class);
        return $service->shouldNotify($module, $event, $channel);
    }
}
