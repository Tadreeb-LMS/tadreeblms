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

        Gate::define('kpi_create', function () {
            return true;
        });

        Gate::define('kpi_edit', function () {
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

    #[Test]
    public function it_blocks_storing_unsupported_kpi_type()
    {
        $admin = $this->loginAsAdmin();
        $category = Category::query()->create([
            'name' => 'KPI Category D',
            'slug' => 'kpi-category-d',
            'status' => 1,
        ]);

        $response = $this->post(route('admin.kpis.store'), [
            'name' => 'Unsupported Type KPI',
            'code' => 'UNSUPPORTED_TYPE_KPI',
            'type' => 'custom_formula',
            'description' => 'Should fail due to unsupported type.',
            'weight' => 10,
            'category_ids' => [$category->id],
            'course_ids' => [],
        ]);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseMissing('kpis', ['code' => 'UNSUPPORTED_TYPE_KPI']);
    }

    #[Test]
    public function it_blocks_updating_to_unsupported_kpi_type()
    {
        $admin = $this->loginAsAdmin();
        $category = Category::query()->create([
            'name' => 'KPI Category E',
            'slug' => 'kpi-category-e',
            'status' => 1,
        ]);

        $kpi = Kpi::query()->create([
            'name' => 'Updatable KPI',
            'code' => 'UPDATABLE_KPI',
            'type' => 'completion',
            'description' => 'Updatable KPI.',
            'weight' => 10,
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $kpi->categories()->sync([$category->id]);

        $response = $this->put(route('admin.kpis.update', $kpi->id), [
            'name' => 'Updatable KPI',
            'code' => 'UPDATABLE_KPI',
            'type' => 'custom_formula',
            'description' => 'Attempt unsupported type update.',
            'weight' => 10,
            'category_ids' => [$category->id],
            'course_ids' => [],
        ]);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseHas('kpis', [
            'id' => $kpi->id,
            'type' => 'completion',
        ]);
    }

    #[Test]
public function it_rejects_store_when_selected_category_already_has_complete_active_configuration()
{
    config()->set('kpi.total_weight_validation.target', 100);
    config()->set('kpi.total_weight_validation.tolerance', 0.01);

    $admin = $this->loginAsAdmin();

    $categoryA = Category::query()->create([
        'name' => 'Category A',
        'slug' => 'category-a',
        'status' => 1,
    ]);

    $categoryB = Category::query()->create([
        'name' => 'Category B',
        'slug' => 'category-b',
        'status' => 1,
    ]);

    $existingKpiOne = Kpi::query()->create([
        'name' => 'Existing KPI One',
        'code' => 'EXISTING_KPI_ONE',
        'type' => 'completion',
        'description' => 'Existing KPI one.',
        'weight' => 60,
        'is_active' => true,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $existingKpiTwo = Kpi::query()->create([
        'name' => 'Existing KPI Two',
        'code' => 'EXISTING_KPI_TWO',
        'type' => 'score',
        'description' => 'Existing KPI two.',
        'weight' => 40,
        'is_active' => true,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $existingKpiOne->categories()->sync([$categoryA->id]);
    $existingKpiTwo->categories()->sync([$categoryA->id]);

    $response = $this->post(
        route('admin.kpis.store'),
        [
            'name' => 'Duplicate KPI',
            'code' => 'DUPLICATE_KPI',
            'type' => 'completion',
            'description' => 'Should not be created.',
            'weight' => 20,
            'category_ids' => [
                $categoryB->id,
                $categoryA->id,
            ],
            'course_ids' => [],
        ]
    );

    $response->assertSessionHasErrors('category_ids');

    $this->assertDatabaseMissing('kpis', [
        'code' => 'DUPLICATE_KPI',
    ]);
}
#[Test]
public function it_allows_store_when_selected_category_has_less_than_complete_active_configuration()
{
    $admin = $this->loginAsAdmin();

    $category = Category::query()->create([
        'name' => 'Incomplete Category',
        'slug' => 'incomplete-category',
        'status' => 1,
    ]);

    $existingKpi = Kpi::query()->create([
        'name' => 'Existing KPI',
        'code' => 'INCOMPLETE_EXISTING_KPI',
        'type' => 'completion',
        'description' => 'Existing KPI.',
        'weight' => 60,
        'is_active' => true,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $existingKpi->categories()->sync([$category->id]);

    $response = $this->post(
        route('admin.kpis.store'),
        [
            'name' => 'Additional KPI',
            'code' => 'ADDITIONAL_KPI',
            'type' => 'score',
            'description' => 'Additional KPI.',
            'weight' => 20,
            'category_ids' => [$category->id],
            'course_ids' => [],
        ]
    );

    $response->assertRedirect(route('admin.kpis.index'));

    $this->assertDatabaseHas('kpis', [
        'code' => 'ADDITIONAL_KPI',
    ]);
}
#[Test]
public function it_allows_editing_an_existing_complete_category_configuration()
{
    $admin = $this->loginAsAdmin();

    $category = Category::query()->create([
        'name' => 'Editable Category',
        'slug' => 'editable-category',
        'status' => 1,
    ]);

    $kpi = Kpi::query()->create([
        'name' => 'Complete KPI',
        'code' => 'COMPLETE_KPI',
        'type' => 'completion',
        'description' => 'Complete KPI.',
        'weight' => 100,
        'is_active' => true,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $kpi->categories()->sync([$category->id]);

    $response = $this->put(
        route('admin.kpis.update', $kpi->id),
        [
            'name' => 'Updated Complete KPI',
            'code' => 'COMPLETE_KPI',
            'type' => 'completion',
            'description' => 'Updated description.',
            'weight' => 100,
            'category_ids' => [$category->id],
            'course_ids' => [],
        ]
    );

    $response->assertRedirect(route('admin.kpis.index'));

    $this->assertDatabaseHas('kpis', [
        'id' => $kpi->id,
        'name' => 'Updated Complete KPI',
        'weight' => 100,
    ]);
}
#[Test]
public function it_ignores_inactive_kpis_when_checking_category_configuration()
{
    $admin = $this->loginAsAdmin();

    $category = Category::query()->create([
        'name' => 'Inactive Category',
        'slug' => 'inactive-category',
        'status' => 1,
    ]);

    $inactiveKpi = Kpi::query()->create([
        'name' => 'Inactive KPI',
        'code' => 'INACTIVE_KPI',
        'type' => 'completion',
        'description' => 'Inactive KPI.',
        'weight' => 100,
        'is_active' => false,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $inactiveKpi->categories()->sync([$category->id]);

    $response = $this->post(
        route('admin.kpis.store'),
        [
            'name' => 'New Active KPI',
            'code' => 'NEW_ACTIVE_KPI',
            'type' => 'score',
            'description' => 'New active KPI.',
            'weight' => 50,
            'category_ids' => [$category->id],
            'course_ids' => [],
        ]
    );

    $response->assertRedirect(route('admin.kpis.index'));

    $this->assertDatabaseHas('kpis', [
        'code' => 'NEW_ACTIVE_KPI',
        'is_active' => true,
    ]);
}
}
