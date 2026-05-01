<?php

namespace App\Events\Kpi;

/**
 * Event fired when a user logs in.
 *
 * Payload structure:
 * - ip_address (string): User's IP address
 * - user_agent (string): User's browser user agent
 *
 * Used for: User engagement metrics, login frequency
 */
class UserLoginEvent extends AbstractKpiEvent
{
    protected string $eventType = 'user_login';

    /**
     * Create a new user login event.
     *
     * @param int $userId
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @param mixed $occurredAt
     */
    public function __construct(int $userId, ?string $ipAddress = null, ?string $userAgent = null, $occurredAt = null)
    {
        $payload = [];

        if ($ipAddress) {
            $payload['ip_address'] = $ipAddress;
        }

        if ($userAgent) {
            $payload['user_agent'] = $userAgent;
        }

        parent::__construct($userId, $payload, $occurredAt);
    }
}
