<?php

namespace Tests\Feature\Backend\Kpi;

use App\Models\Kpi;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KpiEditPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('kpi_edit', function () {
            return true;
        });

        View::share('locales', []);
    }

    #[Test]
    public function it_renders_the_edit_page_for_an_existing_kpi()
    {
        $admin = $this->loginAsAdmin();

        $kpi = Kpi::query()->create([
            'name' => 'Editable KPI',
            'code' => 'EDITABLE_KPI',
            'type' => 'completion',
            'description' => 'KPI used to check the edit page renders.',
            'weight' => 10,
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $response = $this->get(route('admin.kpis.edit', $kpi->id));

        $response->assertOk();
        $response->assertViewIs('backend.kpis.edit');
        $response->assertViewHas('kpi', function ($viewKpi) use ($kpi) {
            return $viewKpi instanceof Kpi && $viewKpi->id === $kpi->id;
        });
    }
}
