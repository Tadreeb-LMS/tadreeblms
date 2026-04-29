<?php

namespace App\Providers;

use App\Events\Kpi\KpiEvent;
use App\Listeners\Kpi\RecordKpiEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Service provider for KPI event system.
 *
 * Registers event listeners with the Laravel event dispatcher.
 * Ensures that all KpiEvent instances are automatically recorded
 * to the lms_kpi_events table.
 *
 * Bootstrap:
 * - Add to config/app.php providers array
 *
 * Listeners:
 * - RecordKpiEventListener: Records all KpiEvent instances
 *
 * Future Extensions:
 * - WebhookEventListener: Send events to external systems
 * - AnalyticsEventListener: Trigger real-time analytics
 * - AuditEventListener: Log event stream for compliance
 */
class KpiEventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // All KpiEvent instances are recorded to lms_kpi_events table
        KpiEvent::class => [
            RecordKpiEventListener::class,
        ],
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind KpiEventDispatcher to service container
        $this->app->bind('kpi.dispatcher', function ($app) {
            return new \App\Services\Kpi\KpiEventDispatcher(
                $app->make('log')
            );
        });
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register all listeners from the $listen property
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }
}
