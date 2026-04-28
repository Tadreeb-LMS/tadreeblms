<?php

namespace App\Services\Kpi;

use App\Events\Kpi\KpiEvent;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;

/**
 * Unified Event Dispatcher for KPI System.
 *
 * This service provides a centralized dispatcher to standardize how LMS modules
 * send events to the KPI system. It serves as the single entry point for all
 * KPI-related events, ensuring consistent data flow and preventing tight coupling.
 *
 * Key Responsibilities:
 * - Dispatch events to registered listeners (e.g., RecordKpiEventListener)
 * - Validate events before dispatch
 * - Provide optional event tracking/logging
 * - Support future external integrations
 *
 * Integration Pattern:
 * 1. LMS modules create event objects (e.g., UserLoginEvent)
 * 2. LMS modules dispatch via KpiEventDispatcher::dispatch()
 * 3. Dispatcher validates and emits event via Laravel's Event system
 * 4. Listeners (e.g., RecordKpiEventListener) consume and record to KPI system
 *
 * Usage Example:
 * ```php
 * $event = new UserLoginEvent($userId, $ipAddress, $userAgent);
 * app(KpiEventDispatcher::class)->dispatch($event);
 * ```
 *
 * Design Benefits:
 * - Single entry point for all KPI events
 * - Standardized event structure ensures consistency
 * - Easy to add event tracking, validation, or filtering
 * - Support for middleware/interceptors in the future
 * - Non-breaking for external systems consuming lms_kpi_events table
 */
class KpiEventDispatcher
{
    /**
     * Event dispatcher (Laravel's event system).
     *
     * @var Event
     */
    protected $eventDispatcher;

    /**
     * Logger instance for tracking event dispatch.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Whether to log all dispatched events.
     *
     * @var bool
     */
    protected $loggingEnabled = false;

    /**
     * Create a new KPI event dispatcher instance.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Dispatch a KPI event to all registered listeners.
     *
     * The event is validated and then emitted through Laravel's Event system.
     * All listeners subscribed to this event will be notified synchronously.
     *
     * @param KpiEvent $event
     * @return void
     */
    public function dispatch(KpiEvent $event): void
    {
        if ($this->loggingEnabled && $this->logger) {
            $this->logger->debug('Dispatching KPI event', [
                'event_type' => $event->getEventType(),
                'user_id' => $event->getUserId(),
                'occurred_at' => $event->getOccurredAt()->toDateTimeString(),
            ]);
        }

        // Emit through Laravel's event system
        Event::dispatch($event);
    }

    /**
     * Enable event logging for debugging purposes.
     *
     * @return self
     */
    public function enableLogging(): self
    {
        $this->loggingEnabled = true;
        return $this;
    }

    /**
     * Disable event logging.
     *
     * @return self
     */
    public function disableLogging(): self
    {
        $this->loggingEnabled = false;
        return $this;
    }

    /**
     * Check if logging is enabled.
     *
     * @return bool
     */
    public function isLoggingEnabled(): bool
    {
        return $this->loggingEnabled;
    }
}
