<?php

namespace Tests\Feature\Backend\Kpi;

use App\Models\Kpi;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArchiveKpiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('category_access', function () {
            return true;
        });
    }

    #[Test]
    public function an_admin_can_archive_a_kpi_via_soft_delete()
    {
        $admin = $this->loginAsAdmin();

        $kpi = Kpi::query()->create([
            'name' => 'Completion KPI',
            'code' => 'COURSE_COMPLETION',
            'type' => 'completion',
            'description' => 'Tracks course completion.',
            'weight' => 25,
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $response = $this->delete(route('admin.kpis.destroy', $kpi->id));

        $response->assertRedirect(route('admin.kpis.index'));
        $response->assertSessionHas(['flash_success' => 'KPI archived successfully.']);

        $this->assertSoftDeleted('kpis', ['id' => $kpi->id]);
        $this->assertDatabaseHas('kpi_status_histories', [
            'kpi_id' => $kpi->id,
            'action' => 'archived',
            'changed_by' => $admin->id,
        ]);
    }
}
