<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

/**
 * KpiUserCursor
 *
 * Stores the processing state for incremental KPI calculations on a per-user,
 * per-KPI basis.  The cursor advances each time new lms_kpi_events are
 * successfully incorporated into computed_value, preventing redundant
 * full-table scans and enabling efficient, scalable KPI processing.
 *
 * @property int         $id
 * @property int         $user_id
 * @property int         $kpi_id
 * @property int|null    $last_event_id     Last lms_kpi_events.id that is included in computed_value
 * @property float|null  $computed_value    Pre-computed KPI percentage 0–100
 * @property int         $event_count       Events contributing to computed_value
 * @property array|null  $checkpoint_data   Type-specific running state
 * @property bool        $is_dirty          True when a full recalculation is needed
 * @property \Carbon\Carbon|null $last_processed_at
 */
class KpiUserCursor extends Model
{
    protected $table = 'kpi_user_cursors';

    protected $fillable = [
        'user_id',
        'kpi_id',
        'last_event_id',
        'computed_value',
        'event_count',
        'checkpoint_data',
        'is_dirty',
        'last_processed_at',
    ];

    protected $casts = [
        'computed_value'    => 'float',
        'event_count'       => 'integer',
        'checkpoint_data'   => 'array',
        'is_dirty'          => 'boolean',
        'last_processed_at' => 'datetime',
        'last_event_id'     => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kpi()
    {
        return $this->belongsTo(Kpi::class, 'kpi_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return the computed value (defaulting to 0 when never calculated).
     */
    public function getEffectiveValue(): float
    {
        return $this->computed_value ?? 0.0;
    }

    /**
     * Return the raw type-specific accumulator stored in checkpoint_data,
     * or a default value when the key is absent.
     */
    public function getCheckpointKey(string $key, $default = 0.0)
    {
        $data = $this->checkpoint_data ?? [];
        return $data[$key] ?? $default;
    }

    /**
     * Mark this cursor as dirty so the next processing run performs a full
     * recalculation rather than an incremental one.
     */
    public function markDirty(): void
    {
        $this->is_dirty = true;
        $this->save();
    }

    /**
     * Reset the cursor back to a clean, unprocessed state.
     */
    public function reset(): void
    {
        $this->last_event_id     = null;
        $this->computed_value    = null;
        $this->event_count       = 0;
        $this->checkpoint_data   = null;
        $this->is_dirty          = false;
        $this->last_processed_at = null;
        $this->save();
    }

    /**
     * Advance the cursor after successfully processing a batch of events.
     *
     * @param  int        $newLastEventId
     * @param  float      $newValue
     * @param  int        $newEventCount
     * @param  array|null $newCheckpointData
     */
    public function advance(int $newLastEventId, float $newValue, int $newEventCount, ?array $newCheckpointData = null): void
    {
        $this->last_event_id     = $newLastEventId;
        $this->computed_value    = round($newValue, 4);
        $this->event_count       = $newEventCount;
        $this->checkpoint_data   = $newCheckpointData;
        $this->is_dirty          = false;
        $this->last_processed_at = now();
        $this->save();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeDirty($query)
    {
        return $query->where('is_dirty', true);
    }

    public function scopeForKpi($query, int $kpiId)
    {
        return $query->where('kpi_id', $kpiId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
