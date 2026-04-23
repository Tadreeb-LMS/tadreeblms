<?php

namespace Tests\Feature\Backend\Kpi;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\Kpi;
use App\Models\KpiTarget;
use App\Services\Kpi\KpiTargetResolver;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KpiTargetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('kpi_target_access', function () {
            return true;
        });

        Gate::define('kpi_target_edit', function () {
            return true;
        });
    }

    private function makeKpi(array $attrs = []): Kpi
    {
        return Kpi::query()->create(array_merge([
            'name' => 'Target KPI',
            'code' => 'TARGET_KPI_' . uniqid(),
            'type' => 'completion',
            'description' => 'Target test',
            'weight' => 2.0,
            'is_active' => true,
        ], $attrs));
    }

    private function makeRole(string $name = null): Role
    {
        $name = $name ?? 'role_' . uniqid();

        return Role::create(['name' => $name, 'guard_name' => 'web']);
    }

    private function makeCourse(): Course
    {
        $category = Category::query()->create([
            'name' => 'KPI Target Category ' . uniqid(),
            'slug' => 'kpi-target-category-' . uniqid(),
            'status' => 1,
        ]);

        return Course::query()->create([
            'title' => 'KPI Target Course ' . uniqid(),
            'category_id' => $category->id,
            'slug' => 'kpi-target-course-' . uniqid(),
            'description' => 'Course for KPI target tests.',
            'price' => 0,
            'published' => 1,
            'featured' => 0,
            'trending' => 0,
            'popular' => 0,
        ]);
    }

    #[Test]
    public function resolver_uses_global_target_when_no_role_or_course_override_exists(): void
    {
        $kpi = $this->makeKpi();

        KpiTarget::query()->create([
            'kpi_id' => $kpi->id,
            'target_value' => 70,
        ]);

        $comparison = app(KpiTargetResolver::class)->resolveComparison($kpi, 63.0, null, []);

        $this->assertSame(70.0, $comparison['target']);
        $this->assertSame('global', $comparison['target_scope']);
        $this->assertSame('under', $comparison['deviation_direction']);
        $this->assertSame(-10.0, $comparison['deviation_percentage']);
    }

    #[Test]
    public function resolver_prefers_role_target_over_global_target(): void
    {
        $kpi = $this->makeKpi();
        $role = $this->makeRole('manager');

        KpiTarget::query()->create([
            'kpi_id' => $kpi->id,
            'target_value' => 60,
        ]);

        KpiTarget::query()->create([
            'kpi_id' => $kpi->id,
            'role_id' => $role->id,
            'target_value' => 80,
        ]);

        $comparison = app(KpiTargetResolver::class)->resolveComparison($kpi, 84.0, $role->id, []);

        $this->assertSame(80.0, $comparison['target']);
        $this->assertSame('role', $comparison['target_scope']);
        $this->assertSame('over', $comparison['deviation_direction']);
        $this->assertSame(5.0, $comparison['deviation_percentage']);
    }

    #[Test]
    public function resolver_can_use_course_specific_target(): void
    {
        $kpi = $this->makeKpi();
        $course = $this->makeCourse();

        KpiTarget::query()->create([
            'kpi_id' => $kpi->id,
            'course_id' => $course->id,
            'target_value' => 50,
        ]);

        $comparison = app(KpiTargetResolver::class)->resolveComparison($kpi, 50.0, null, [$course->id]);

        $this->assertSame(50.0, $comparison['target']);
        $this->assertSame('course', $comparison['target_scope']);
        $this->assertSame('on_target', $comparison['deviation_direction']);
        $this->assertSame(0.0, $comparison['deviation_percentage']);
    }

    #[Test]
    public function admin_can_store_and_delete_kpi_target(): void
    {
        $this->loginAsAdmin();

        $kpi = $this->makeKpi();

        $store = $this->post(route('admin.kpi-targets.store'), [
            'kpi_id' => $kpi->id,
            'target_value' => 75.5,
        ]);

        $store->assertRedirect();

        $target = KpiTarget::query()->where('kpi_id', $kpi->id)->first();
        $this->assertNotNull($target);
        $this->assertSame(75.5, (float) $target->target_value);

        $delete = $this->delete(route('admin.kpi-targets.destroy', $target->id));

        $delete->assertRedirect();
        $this->assertDatabaseMissing('kpi_targets', ['id' => $target->id]);
    }

    #[Test]
    public function unauthorized_user_cannot_modify_kpi_targets(): void
    {
        Gate::define('kpi_target_edit', function () {
            return false;
        });

        $teacherRole = Role::query()->firstOrCreate([
            'name' => 'teacher',
            'guard_name' => 'web',
        ]);

        /** @var User $teacher */
        $teacher = factory(User::class)->create();
        $teacher->assignRole($teacherRole);
        $this->actingAs($teacher);

        $kpi = $this->makeKpi();

        $store = $this->post(route('admin.kpi-targets.store'), [
            'kpi_id' => $kpi->id,
            'target_value' => 40,
        ]);

        $store->assertStatus(401);
        $this->assertDatabaseMissing('kpi_targets', ['kpi_id' => $kpi->id]);
    }
}
