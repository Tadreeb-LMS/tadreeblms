<?php

namespace App\Models;

use App\Models\Auth\Role;
use Illuminate\Database\Eloquent\Model;

class KpiRoleConfig extends Model
{
    protected $fillable = [
        'role_id',
        'kpi_id',
        'weight_override',
        'is_active_override',
    ];

    protected $casts = [
        'weight_override'    => 'float',
        'is_active_override' => 'boolean',
    ];

    // ——— Relationships ———————————————————————————————————————

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // ——— Helpers ———————————————————————————————————————————

    /**
     * Merge this role override on top of the base KPI's weight/active defaults.
     * Returns the effective config array used by KpiProcessingEngine.
     *
     * @param  Kpi  $kpi
     * @return array{type: string, weight: float, is_active: bool}
     */
    public function effectiveConfigFor(Kpi $kpi): array
    {
        return [
            'type'      => $kpi->type,
            'weight'    => $this->weight_override !== null ? (float) $this->weight_override : (float) $kpi->weight,
            'is_active' => $this->is_active_override !== null ? (bool) $this->is_active_override : (bool) $kpi->is_active,
        ];
    }
}
