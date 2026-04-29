<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LmsEventRecorder
{
    const TYPE_USER_LOGIN = 'user_login';
    const TYPE_QUIZ_ATTEMPT = 'quiz_attempt';
    const TYPE_COURSE_COMPLETED = 'course_completed';

    /**
     * Persist a single LMS event.
     *
     * @param int|null $userId
     * @param string $eventType
     * @param array $payload
     * @param \Carbon\Carbon|string|null $occurredAt
     * @return bool
     */
    public function record($userId, $eventType, array $payload = [], $occurredAt = null)
    {
        $row = [
            'user_id' => $userId,
            'event_type' => (string) $eventType,
            'occurred_at' => $this->normalizeOccurredAt($occurredAt),
            'payload' => empty($payload) ? null : json_encode($payload),
            'created_at' => now(),
        ];

        try {
            DB::table('lms_kpi_events')->insert($row);
            return true;
        } catch (\Throwable $e) {
            // Event capture must never break the user flow.
            Log::warning('Unable to persist LMS KPI event.', [
                'event_type' => $eventType,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Persist multiple LMS events in chunks.
     *
     * @param array $events
     * @return int
     */
    public function recordMany(array $events)
    {
        if (empty($events)) {
            return 0;
        }

        $rows = [];
        foreach ($events as $event) {
            $rows[] = [
                'user_id' => $event['user_id'] ?? null,
                'event_type' => (string) ($event['event_type'] ?? ''),
                'occurred_at' => $this->normalizeOccurredAt($event['occurred_at'] ?? null),
                'payload' => empty($event['payload']) ? null : json_encode($event['payload']),
                'created_at' => now(),
            ];
        }

        $inserted = 0;
        foreach (array_chunk($rows, 500) as $chunk) {
            try {
                DB::table('lms_kpi_events')->insert($chunk);
                $inserted += count($chunk);
            } catch (\Throwable $e) {
                Log::warning('Unable to persist chunk of LMS KPI events.', [
                    'events_count' => count($chunk),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $inserted;
    }

    /**
     * @param \Carbon\Carbon|string|null $occurredAt
     * @return string
     */
    protected function normalizeOccurredAt($occurredAt)
    {
        try {
            if ($occurredAt instanceof Carbon) {
                return $occurredAt->toDateTimeString();
            }

            if (is_string($occurredAt) && trim($occurredAt) !== '') {
                return Carbon::parse($occurredAt)->toDateTimeString();
            }
        } catch (\Throwable $e) {
            Log::warning('Invalid occurred_at provided for LMS KPI event, falling back to now.', [
                'occurred_at' => is_scalar($occurredAt) ? (string) $occurredAt : gettype($occurredAt),
                'error' => $e->getMessage(),
            ]);
        }

        return now()->toDateTimeString();
    }
}
