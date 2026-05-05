<?php

namespace App\Services\Kpi;

use App\Models\Auth\User;
use App\Models\Kpi;
use App\Models\KpiRoleConfig;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves the effective KPI configuration (weight, is_active) for a specific role.
 *
 * Priority: role-specific override → KPI global default.
 */
class KpiRoleConfigResolver
{
    /**
     * Return the effective KPI config array for the given role ID.
     *
     * @param  Kpi    $kpi
     * @param  int|null $roleId  Spatie role ID; null means no role — fall back to defaults.
     * @return array{type: string, weight: float, is_active: bool}
     */
    public function resolve(Kpi $kpi, ?int $roleId): array
    {
        $default = [
            'type'      => $kpi->type,
            'weight'    => (float) $kpi->weight,
            'is_active' => (bool) $kpi->is_active,
        ];

        if ($roleId === null || !$this->tableExists()) {
            return $default;
        }

        /** @var KpiRoleConfig|null $override */
        $override = KpiRoleConfig::query()
            ->where('role_id', $roleId)
            ->where('kpi_id', $kpi->id)
            ->first();

        if ($override === null) {
            return $default;
        }

        return $override->effectiveConfigFor($kpi);
    }

    /**
     * Resolve the primary role ID for a user.
     * Returns null if the user has no roles or the roles table doesn't exist yet.
     *
     * @param  User  $user
     * @return int|null
     */
    public function primaryRoleIdForUser(User $user): ?int
    {
        if (!$this->tableExists()) {
            return null;
        }

        $role = $user->roles()->orderBy('id')->first();

        return $role ? (int) $role->id : null;
    }

    /**
     * Build a keyed map of all role overrides for a set of KPI IDs and one role.
     * Useful for batch look-ups.
     *
     * @param  int[]  $kpiIds
     * @param  int    $roleId
     * @return array<int, KpiRoleConfig>   keyed by kpi_id
     */
    public function loadOverridesForRole(array $kpiIds, int $roleId): array
    {
        if (empty($kpiIds) || !$this->tableExists()) {
            return [];
        }

        return KpiRoleConfig::query()
            ->where('role_id', $roleId)
            ->whereIn('kpi_id', $kpiIds)
            ->get()
            ->keyBy('kpi_id')
            ->all();
    }

    protected function tableExists(): bool
    {
        return Schema::hasTable('kpi_role_configs');
    }
}
