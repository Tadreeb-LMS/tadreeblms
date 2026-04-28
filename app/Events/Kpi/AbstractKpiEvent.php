<?php

namespace App\Events\Kpi;

use Carbon\Carbon;

/**
 * Abstract base class for KPI events.
 *
 * Provides common functionality for all KPI events:
 * - Event type management
 * - User ID association
 * - Occurred-at timestamp normalization
 * - Payload standardization
 *
 * Concrete event classes should extend this and provide their own
 * payload structure and event type constant.
 */
abstract class AbstractKpiEvent implements KpiEvent
{
    /**
     * Event type identifier (snake_case).
     *
     * @var string
     */
    protected string $eventType;

    /**
     * User ID associated with the event.
     *
     * @var int|null
     */
    protected ?int $userId;

    /**
     * Timestamp when the event occurred.
     *
     * @var Carbon
     */
    protected Carbon $occurredAt;

    /**
     * Event payload (business data).
     *
     * @var array
     */
    protected array $payload = [];

    /**
     * Create a new KPI event instance.
     *
     * @param int|null $userId
     * @param array $payload
     * @param Carbon|string|null $occurredAt
     */
    public function __construct(?int $userId = null, array $payload = [], $occurredAt = null)
    {
        $this->userId = $userId;
        $this->payload = $payload;
        $this->occurredAt = $this->normalizeOccurredAt($occurredAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * {@inheritDoc}
     */
    public function getOccurredAt(): Carbon
    {
        return $this->occurredAt;
    }

    /**
     * {@inheritDoc}
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'event_type' => $this->eventType,
            'occurred_at' => $this->occurredAt->toDateTimeString(),
            'payload' => $this->payload,
        ];
    }

    /**
     * Normalize the occurred_at timestamp.
     *
     * @param Carbon|string|null $occurredAt
     * @return Carbon
     */
    protected function normalizeOccurredAt($occurredAt): Carbon
    {
        if ($occurredAt instanceof Carbon) {
            return $occurredAt;
        }

        if (is_string($occurredAt) && trim($occurredAt) !== '') {
            return Carbon::parse($occurredAt);
        }

        return Carbon::now();
    }
}
