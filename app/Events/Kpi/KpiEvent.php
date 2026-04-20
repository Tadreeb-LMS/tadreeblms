<?php

namespace App\Events\Kpi;

use Carbon\Carbon;

/**
 * Base interface for KPI-related events.
 *
 * All events dispatched through the KPI event system must implement
 * this interface. This ensures consistent event structure, standardized
 * payload handling, and predictable event flow.
 *
 * Event Payload Structure:
 * - All events include a 'occurred_at' timestamp (defaults to now)
 * - Custom data is stored in a standardized 'payload' array
 * - Event metadata is always separable from business data
 *
 * Integration Points:
 * - Events are dispatched via KpiEventDispatcher
 * - Listeners consume events and record to lms_kpi_events table
 * - No KPI calculation logic should exist in event classes
 * - Events are immutable once created (design for testability)
 */
interface KpiEvent
{
    /**
     * Get the event type identifier (e.g., 'user_login', 'quiz_attempt').
     *
     * @return string
     */
    public function getEventType(): string;

    /**
     * Get the user ID associated with this event (nullable for system events).
     *
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * Get the timestamp when the event occurred.
     *
     * @return Carbon
     */
    public function getOccurredAt(): Carbon;

    /**
     * Get the event payload (business data).
     *
     * The payload contains event-specific data needed for KPI calculations
     * or external integrations. All keys must be snake_case for consistency.
     *
     * @return array
     */
    public function getPayload(): array;

    /**
     * Convert event to standardized array for storage or transmission.
     *
     * @return array
     */
    public function toArray(): array;
}
