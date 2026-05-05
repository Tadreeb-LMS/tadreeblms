<?php

namespace Tests\Feature\Backend\Kpi;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Kpi;
use App\Models\KpiTemplate;
use App\Models\KpiTemplateItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class KpiTemplateControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Backend layout header expects this shared variable in tests.
        View::share('locales', ['en' => 'English']);

        Gate::define('kpi_template_access', function () {
            return true;
        });

        Gate::define('kpi_template_edit', function () {
            return true;
        });

        Gate::define('kpi_template_create', function () {
            return true;
        });
    }

    private function makeTemplate(string $suffix = 'default'): KpiTemplate
    {
        $template = KpiTemplate::query()->create([
            'name' => 'Template ' . $suffix,
            'slug' => 'template-' . $suffix,
            'category' => 'compliance',
            'description' => 'Template description',
            'use_case' => 'Compliance baseline',
            'is_active' => true,
        ]);

        KpiTemplateItem::query()->create([
            'template_id' => $template->id,
            'name' => 'Completion KPI',
            'code' => 'TPL_' . strtoupper($suffix) . '_COMP',
            'description' => 'Completion metric',
            'type' => 'percentage',
            'weight' => 60,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        KpiTemplateItem::query()->create([
            'template_id' => $template->id,
            'name' => 'Certification KPI',
            'code' => 'TPL_' . strtoupper($suffix) . '_CERT',
            'description' => 'Certification metric',
            'type' => 'percentage',
            'weight' => 40,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        return $template;
    }

    public function test_admin_can_open_template_gallery(): void
    {
        $this->loginAsAdmin();
        $this->makeTemplate('gallery');

        $response = $this->get(route('admin.kpi-templates.index'));

        $response->assertStatus(200);
        $response->assertSee('KPI Templates');
        $response->assertSee('Preview');
        $response->assertSee('Apply');
    }

    public function test_admin_can_preview_template_before_apply(): void
    {
        $this->loginAsAdmin();
        $template = $this->makeTemplate('preview');

        $response = $this->get(route('admin.kpi-templates.show', $template->id));

        $response->assertStatus(200);
        $response->assertSee('KPIs to be Created');
        $response->assertSee('Completion KPI');
        $response->assertSee('Apply This Template');
    }

    public function test_apply_creates_kpis_and_does_not_overwrite_existing(): void
    {
        $this->loginAsAdmin();
        $template = $this->makeTemplate('safe');

        Kpi::query()->create([
            'name' => 'Existing Completion KPI',
            'code' => 'TPL_SAFE_COMP',
            'type' => 'percentage',
            'description' => 'Existing metric should be preserved',
            'weight' => 10,
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.kpi-templates.apply', $template->id), [
            'skip_existing' => 1,
        ]);

        $response->assertRedirect(route('admin.kpis.index'));

        // Existing code should still be single-row (not overwritten/duplicated)
        $this->assertSame(1, Kpi::query()->where('code', 'TPL_SAFE_COMP')->count());

        // Non-conflicting KPI should be created
        $this->assertDatabaseHas('kpis', [
            'code' => 'TPL_SAFE_CERT',
            'name' => 'Certification KPI',
        ]);
    }

    public function test_preview_shows_conflicts_for_existing_codes(): void
    {
        $this->loginAsAdmin();
        $template = $this->makeTemplate('conflict');

        Kpi::query()->create([
            'name' => 'Existing Completion KPI',
            'code' => 'TPL_CONFLICT_COMP',
            'type' => 'percentage',
            'description' => 'Existing metric',
            'weight' => 20,
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.kpi-templates.show', $template->id));

        $response->assertStatus(200);
        $response->assertSee('Will be Skipped');
        $response->assertSee('Existing Completion KPI');
    }

    public function test_teacher_without_permissions_cannot_access_templates(): void
    {
        Gate::define('kpi_template_access', function () {
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
        $template = $this->makeTemplate('deny');

        $this->get(route('admin.kpi-templates.index'))->assertStatus(401);
        $this->get(route('admin.kpi-templates.show', $template->id))->assertStatus(401);
    }

    public function test_teacher_with_read_only_access_cannot_apply_template(): void
    {
        Gate::define('kpi_template_access', function () {
            return true;
        });

        Gate::define('kpi_template_edit', function () {
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
        $template = $this->makeTemplate('readonly');

        $this->post(route('admin.kpi-templates.apply', $template->id))->assertStatus(401);
    }

    public function test_admin_can_create_custom_template(): void
    {
        $this->loginAsAdmin();

        $response = $this->post(route('admin.kpi-templates.store'), [
            'name' => 'Custom Compliance Pack',
            'category' => 'compliance',
            'description' => 'Custom pack for compliance onboarding.',
            'use_case' => 'For new-joiner onboarding.',
            'items' => [
                [
                    'name' => 'Completion Rate',
                    'code' => 'CUSTOM_COMPLETION',
                    'description' => 'Completion metric',
                    'type' => 'percentage',
                    'weight' => 70,
                    'is_active' => 1,
                ],
                [
                    'name' => 'Certification Rate',
                    'code' => 'CUSTOM_CERT',
                    'description' => 'Certification metric',
                    'type' => 'percentage',
                    'weight' => 30,
                    'is_active' => 1,
                ],
            ],
        ]);

        $template = KpiTemplate::query()->where('name', 'Custom Compliance Pack')->first();

        $this->assertNotNull($template);
        $response->assertRedirect(route('admin.kpi-templates.show', $template->id));
        $this->assertDatabaseHas('kpi_template_items', [
            'template_id' => $template->id,
            'code' => 'CUSTOM_COMPLETION',
        ]);
        $this->assertDatabaseHas('kpi_template_items', [
            'template_id' => $template->id,
            'code' => 'CUSTOM_CERT',
        ]);
    }

    public function test_teacher_without_create_permission_cannot_create_template(): void
    {
        Gate::define('kpi_template_create', function () {
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

        $this->post(route('admin.kpi-templates.store'), [
            'name' => 'Forbidden Template',
            'category' => 'general',
            'items' => [
                [
                    'name' => 'Item',
                    'code' => 'FORBIDDEN_ITEM',
                    'type' => 'percentage',
                    'weight' => 100,
                    'is_active' => 1,
                ],
            ],
        ])->assertStatus(401);

        $this->assertDatabaseMissing('kpi_templates', [
            'name' => 'Forbidden Template',
        ]);
    }

    public function test_admin_can_create_template_from_existing_kpis_only(): void
    {
        $this->loginAsAdmin();

        $kpiA = Kpi::query()->create([
            'name' => 'Existing KPI A',
            'code' => 'EXISTING_KPI_A',
            'type' => 'percentage',
            'description' => 'Existing A',
            'weight' => 55,
            'is_active' => true,
        ]);

        $kpiB = Kpi::query()->create([
            'name' => 'Existing KPI B',
            'code' => 'EXISTING_KPI_B',
            'type' => 'numeric',
            'description' => 'Existing B',
            'weight' => 45,
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.kpi-templates.store'), [
            'name' => 'From Existing KPIs',
            'category' => 'general',
            'existing_kpi_ids' => [$kpiA->id, $kpiB->id],
            'items' => [],
        ]);

        $template = KpiTemplate::query()->where('name', 'From Existing KPIs')->first();

        $this->assertNotNull($template);
        $response->assertRedirect(route('admin.kpi-templates.show', $template->id));
        $this->assertDatabaseHas('kpi_template_items', [
            'template_id' => $template->id,
            'code' => 'EXISTING_KPI_A',
            'name' => 'Existing KPI A',
        ]);
        $this->assertDatabaseHas('kpi_template_items', [
            'template_id' => $template->id,
            'code' => 'EXISTING_KPI_B',
            'name' => 'Existing KPI B',
        ]);
    }
}
