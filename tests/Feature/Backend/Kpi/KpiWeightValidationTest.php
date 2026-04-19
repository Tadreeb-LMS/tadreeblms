<?php

namespace Tests\Feature\Backend\Kpi;

use App\Models\Category;
use App\Models\Kpi;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KpiWeightValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('category_access', function () {
            return true;
        });
    }

    #[Test]
    public function it_rejects_store_when_optional_total_weight_validation_is_enabled_and_total_is_off_target()
    {
        config()->set('kpi.total_weight_validation.enabled', true);
        config()->set('kpi.total_weight_validation.target', 100);
        config()->set('kpi.total_weight_validation.tolerance', 0.01);

        $admin = $this->loginAsAdmin();
        $category = Category::query()->create([
            'name' => 'KPI Category A',
            'slug' => 'kpi-category-a',
            'status' => 1,
        ]);

        Kpi::query()->create([
            'name' => 'Existing KPI',
            'code' => 'EXISTING_KPI',
            'type' => 'completion',
            'description' => 'Existing KPI.',
            'weight' => 30,
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $response = $this->post(route('admin.kpis.store'), [
            'name' => 'New KPI',
            'code' => 'NEW_KPI',
            'type' => 'score',
            'description' => 'New KPI description.',
            'weight' => 20,
            'category_ids' => [$category->id],
            'course_ids' => [],
        ]);

        $response->assertSessionHasErrors('weight');
        $this->assertDatabaseMissing('kpis', ['code' => 'NEW_KPI']);
    }

    #[Test]
    public function it_rejects_update_when_optional_total_weight_validation_is_enabled_and_total_is_off_target()
    {
        config()->set('kpi.total_weight_validation.enabled', true);
        config()->set('kpi.total_weight_validation.target', 100);
        config()->set('kpi.total_weight_validation.tolerance', 0.01);

        $admin = $this->loginAsAdmin();
        $category = Category::query()->create([
            'name' => 'KPI Category B',
            'slug' => 'kpi-category-b',
            'status' => 1,
        ]);

        Kpi::query()->create([
            'name' => 'Anchor KPI',
            'code' => 'ANCHOR_KPI',
            'type' => 'completion',
            'description' => 'Anchor KPI.',
            'weight' => 60,
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $kpi = Kpi::query()->create([
            'name' => 'Editable KPI',
            'code' => 'EDITABLE_KPI',
            'type' => 'score',
            'description' => 'Editable KPI.',
            'weight' => 40,
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $kpi->categories()->sync([$category->id]);

        $response = $this->put(route('admin.kpis.update', $kpi->id), [
            'name' => 'Editable KPI',
            'code' => 'EDITABLE_KPI',
            'type' => 'score',
            'description' => 'Editable KPI updated.',
            'weight' => 10,
            'category_ids' => [$category->id],
            'course_ids' => [],
        ]);

        $response->assertSessionHasErrors('weight');
        $this->assertDatabaseHas('kpis', [
            'id' => $kpi->id,
            'weight' => 40,
        ]);
    }

    #[Test]
    public function it_flashes_warning_after_storing_an_extreme_weight_configuration()
    {
        config()->set('kpi.total_weight_validation.enabled', false);
        config()->set('kpi.extreme_weight_warning_threshold', 70);

        $admin = $this->loginAsAdmin();
        $category = Category::query()->create([
            'name' => 'KPI Category C',
            'slug' => 'kpi-category-c',
            'status' => 1,
        ]);

        $response = $this->post(route('admin.kpis.store'), [
            'name' => 'High Weight KPI',
            'code' => 'HIGH_WEIGHT_KPI',
            'type' => 'completion',
            'description' => 'High weight KPI.',
            'weight' => 80,
            'category_ids' => [$category->id],
            'course_ids' => [],
        ]);

        $response->assertRedirect(route('admin.kpis.index'));
        $response->assertSessionHas('flash_warning');
    }
}
