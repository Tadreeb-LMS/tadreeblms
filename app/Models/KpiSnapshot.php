<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiSnapshot extends Model
{
    protected $fillable = [
        'kpi_id',
        'previous_snapshot_id',
        'calculation_version',
        'input_signature',
        'excluded',
        'value',
        'weighted_score',
        'total_active_weight',
        'is_current',
        'calculated_at',
        'meta',
    ];

    protected $casts = [
        'excluded' => 'boolean',
        'is_current' => 'boolean',
        'value' => 'float',
        'weighted_score' => 'float',
        'total_active_weight' => 'float',
        'calculated_at' => 'datetime',
        'meta' => 'array',
    ];

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function previousSnapshot()
    {
        return $this->belongsTo(self::class, 'previous_snapshot_id');
    }
}
