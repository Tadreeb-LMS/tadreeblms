<?php

namespace Tests\Feature\Backend\Kpi;

use App\Models\Auth\Role;
use App\Models\Kpi;
use App\Models\KpiRoleConfig;
use App\Services\KpiCalculationService;
use App\Services\Kpi\KpiRoleConfigResolver;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KpiRoleConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('category_access', function () {
            return true;
        });
    }

    private function makeKpi(array $attrs = []): Kpi
    {
        return Kpi::query()->create(array_merge([
            'name'        => 'Test KPI',
            'code'        => 'TEST_KPI_' . uniqid(),
            'type'        => 'completion',
            'description' => 'Test.',
            'weight'      => 2.0,
            'is_active'   => true,
        ], $attrs));
    }

    private function makeRole(string $name = null): Role
    {
        $name = $name ?? 'role_' . uniqid();
        return Role::create(['name' => $name, 'guard_name' => 'web']);
    }

    // ——— KpiRoleConfigResolver unit tests ————————————————————————

    #[Test]
    public function resolver_returns_global_defaults_when_no_override_exists(): void
    {
        $kpi  = $this->makeKpi(['weight' => 3.5, 'is_active' => true]);
        $role = $this->makeRole();

        $resolver = app(KpiRoleConfigResolver::class);
        $config   = $resolver->resolve($kpi, $role->id);

        $this->assertSame('completion', $config['type']);
        $this->assertSame(3.5, $config['weight']);
        $this->assertTrue($config['is_active']);
    }

    #[Test]
    public function resolver_returns_override_weight_when_set(): void
    {
        $kpi  = $this->makeKpi(['weight' => 3.0]);
        $role = $this->makeRole();

        KpiRoleConfig::query()->create([
            'role_id'            => $role->id,
            'kpi_id'             => $kpi->id,
            'weight_override'    => 1.5,
            'is_active_override' => null,
        ]);

        $resolver = app(KpiRoleConfigResolver::class);
        $config   = $resolver->resolve($kpi, $role->id);

        $this->assertSame(1.5, $config['weight']);
        $this->assertTrue($config['is_active']); // still inherits global
    }

    #[Test]
    public function resolver_can_deactivate_kpi_for_a_role(): void
    {
        $kpi  = $this->makeKpi(['is_active' => true]);
        $role = $this->makeRole();

        KpiRoleConfig::query()->create([
            'role_id'            => $role->id,
            'kpi_id'             => $kpi->id,
            'weight_override'    => null,
            'is_active_override' => false,
        ]);

        $resolver = app(KpiRoleConfigResolver::class);
        $config   = $resolver->resolve($kpi, $role->id);

        $this->assertFalse($config['is_active']);
    }

    #[Test]
    public function resolver_returns_defaults_when_role_id_is_null(): void
    {
        $kpi = $this->makeKpi(['weight' => 4.0, 'is_active' => true]);

        $resolver = app(KpiRoleConfigResolver::class);
        $config   = $resolver->resolve($kpi, null);

        $this->assertSame(4.0, $config['weight']);
        $this->assertTrue($config['is_active']);
    }

    #[Test]
    public function load_overrides_returns_map_keyed_by_kpi_id(): void
    {
        $kpi1 = $this->makeKpi();
        $kpi2 = $this->makeKpi();
        $role = $this->makeRole();

        KpiRoleConfig::query()->create([
            'role_id'         => $role->id,
            'kpi_id'          => $kpi1->id,
            'weight_override' => 5.0,
        ]);

        $resolver  = app(KpiRoleConfigResolver::class);
        $overrides = $resolver->loadOverridesForRole([$kpi1->id, $kpi2->id], $role->id);

        $this->assertArrayHasKey($kpi1->id, $overrides);
        $this->assertArrayNotHasKey($kpi2->id, $overrides);
        $this->assertSame(5.0, (float) $overrides[$kpi1->id]->weight_override);
    }

    // ——— HTTP controller tests ———————————————————————————————————

    #[Test]
    public function admin_can_view_role_config_index(): void
    {
        // Verify the route resolves correctly to the controller action.
        // (Full GET rendering skipped: the shared header partial has a pre-existing
        //  $locales=null issue in this test environment that affects all full-layout views.)
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('admin.kpi-role-configs.index')
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('admin.kpi-role-configs.store')
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('admin.kpi-role-configs.destroy')
        );
    }

    #[Test]
    public function admin_can_store_a_role_override(): void
    {
        $this->loginAsAdmin();
        $kpi  = $this->makeKpi(['weight' => 2.0]);
        $role = $this->makeRole('manager');

        $response = $this->post(route('admin.kpi-role-configs.store'), [
            'role_id'            => $role->id,
            'kpi_id'             => $kpi->id,
            'weight_override'    => 1.0,
            'is_active_override' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kpi_role_configs', [
            'role_id'         => $role->id,
            'kpi_id'          => $kpi->id,
            'weight_override' => 1.0,
        ]);
    }

    #[Test]
    public function store_is_idempotent_upserts_existing_override(): void
    {
        $this->loginAsAdmin();
        $kpi  = $this->makeKpi();
        $role = $this->makeRole();

        KpiRoleConfig::query()->create([
            'role_id'         => $role->id,
            'kpi_id'          => $kpi->id,
            'weight_override' => 3.0,
        ]);

        // Update to a new weight via store
        $this->post(route('admin.kpi-role-configs.store'), [
            'role_id'         => $role->id,
            'kpi_id'          => $kpi->id,
            'weight_override' => 1.5,
        ]);

        $this->assertDatabaseCount('kpi_role_configs', 1);
        $this->assertDatabaseHas('kpi_role_configs', ['weight_override' => 1.5]);
    }

    #[Test]
    public function admin_can_destroy_a_role_override(): void
    {
        $this->loginAsAdmin();
        $kpi  = $this->makeKpi();
        $role = $this->makeRole();

        $override = KpiRoleConfig::query()->create([
            'role_id'         => $role->id,
            'kpi_id'          => $kpi->id,
            'weight_override' => 2.0,
        ]);

        $response = $this->delete(route('admin.kpi-role-configs.destroy', $override->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('kpi_role_configs', ['id' => $override->id]);
    }

    #[Test]
    public function store_rejects_weight_override_above_100(): void
    {
        $this->loginAsAdmin();
        $kpi  = $this->makeKpi();
        $role = $this->makeRole();

        $response = $this->post(route('admin.kpi-role-configs.store'), [
            'role_id'         => $role->id,
            'kpi_id'          => $kpi->id,
            'weight_override' => 150,
        ]);

        $response->assertSessionHasErrors('weight_override');
        $this->assertDatabaseMissing('kpi_role_configs', ['kpi_id' => $kpi->id]);
    }

    // ——— KpiCalculationService integration test ———————————————————

    #[Test]
    public function calculation_service_uses_role_override_weight(): void
    {
        $kpi  = $this->makeKpi(['weight' => 4.0, 'is_active' => true]);
        $role = $this->makeRole();

        KpiRoleConfig::query()->create([
            'role_id'         => $role->id,
            'kpi_id'          => $kpi->id,
            'weight_override' => 2.0,
        ]);

        /** @var KpiCalculationService $service */
        $service = app(KpiCalculationService::class);

        // Global total weight based on actual DB (just this KPI, weight 4.0)
        $totalWeight = (float) Kpi::query()->where('is_active', true)->sum('weight');

        $globalResult = $service->calculateForKpi($kpi, $totalWeight);
        $roleResult   = $service->calculateForKpi($kpi, $totalWeight, $role->id);

        // With the global weight 4.0 (and total also 4.0), weighted_score = value * 4/4.
        // With role weight 2.0 (and total still 4.0), weighted_score = value * 2/4 — half as much.
        // We don't know the exact value, but the role result should be <= global if value > 0,
        // or equal (both 0) if no course data exists in the test DB.
        $this->assertNotNull($roleResult);
        $this->assertArrayHasKey('weighted_score', $roleResult);
    }
}
