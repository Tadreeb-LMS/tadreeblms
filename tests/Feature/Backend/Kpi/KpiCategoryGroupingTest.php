<?php

namespace Tests\Feature\Backend\Kpi;

use App\Models\Category;
use App\Models\Kpi;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KpiCategoryGroupingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('kpi_access', function () {
            return true;
        });

        View::share('locales', []);
    }

    #[Test]
    public function it_shows_kpi_groups_and_mapped_categories_on_kpi_index()
    {
        $admin = $this->loginAsAdmin();

        $performance = Category::query()->create([
            'name' => 'Performance',
            'slug' => 'performance',
            'status' => 1,
        ]);

        $engagement = Category::query()->create([
            'name' => 'Engagement',
            'slug' => 'engagement',
            'status' => 1,
        ]);

        $kpiA = $this->createKpi($admin->id, 'KPI Throughput', 'KPI_THROUGHPUT', 'completion', 40);
        $kpiB = $this->createKpi($admin->id, 'KPI Activity', 'KPI_ACTIVITY', 'score', 30);

        $kpiA->categories()->sync([$performance->id]);
        $kpiB->categories()->sync([$engagement->id]);

        $response = $this->get(route('admin.kpis.index'));

        $response->assertOk();
        $response->assertSee('KPI Category Groups');
        $response->assertSee('Performance');
        $response->assertSee('Engagement');
        $response->assertSee('Mapped Categories');
    }

    #[Test]
    public function it_filters_kpis_by_selected_category()
    {
        $admin = $this->loginAsAdmin();

        $performance = Category::query()->create([
            'name' => 'Performance',
            'slug' => 'performance',
            'status' => 1,
        ]);

        $compliance = Category::query()->create([
            'name' => 'Compliance',
            'slug' => 'compliance',
            'status' => 1,
        ]);

        $kpiA = $this->createKpi($admin->id, 'Performance KPI', 'PERFORMANCE_KPI', 'completion', 50);
        $kpiB = $this->createKpi($admin->id, 'Compliance KPI', 'COMPLIANCE_KPI', 'score', 50);

        $kpiA->categories()->sync([$performance->id]);
        $kpiB->categories()->sync([$compliance->id]);

        $response = $this->get(route('admin.kpis.index', ['category_id' => $performance->id]));

        $response->assertOk();
        $content = (string) $response->getContent();
        $this->assertStringContainsString('Performance KPI', $content);
        $this->assertStringNotContainsString('Compliance KPI', $content);
    }

    #[Test]
    public function it_includes_grouped_html_in_ajax_response_and_applies_category_filter_to_groups()
    {
        $admin = $this->loginAsAdmin();

        $performance = Category::query()->create([
            'name' => 'Performance',
            'slug' => 'performance',
            'status' => 1,
        ]);

        $engagement = Category::query()->create([
            'name' => 'Engagement',
            'slug' => 'engagement',
            'status' => 1,
        ]);

        $kpiA = $this->createKpi($admin->id, 'Completion KPI', 'COMPLETION_KPI', 'completion', 55);
        $kpiB = $this->createKpi($admin->id, 'Engagement KPI', 'ENGAGEMENT_KPI', 'score', 45);

        $kpiA->categories()->sync([$performance->id]);
        $kpiB->categories()->sync([$engagement->id]);

        $response = $this->getJson(route('admin.kpis.index', ['category_id' => $performance->id]), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'html',
            'groupedHtml',
            'totalActiveWeight',
        ]);

        $payload = $response->json();
        $this->assertStringContainsString('Completion KPI', (string) ($payload['html'] ?? ''));
        $this->assertStringNotContainsString('Engagement KPI', (string) ($payload['html'] ?? ''));
        $this->assertStringContainsString('Performance', (string) ($payload['groupedHtml'] ?? ''));
        $this->assertStringNotContainsString('Engagement', (string) ($payload['groupedHtml'] ?? ''));
    }

    #[Test]
    public function it_counts_a_multi_mapped_kpi_in_each_category_group()
    {
        $admin = $this->loginAsAdmin();

        $performance = Category::query()->create([
            'name' => 'Performance',
            'slug' => 'performance',
            'status' => 1,
        ]);

        $compliance = Category::query()->create([
            'name' => 'Compliance',
            'slug' => 'compliance',
            'status' => 1,
        ]);

        $shared = $this->createKpi($admin->id, 'Shared KPI', 'SHARED_KPI', 'completion', 100);
        $shared->categories()->sync([$performance->id, $compliance->id]);

        $response = $this->get(route('admin.kpis.index'));
        $response->assertOk();

        $content = (string) $response->getContent();
        $this->assertSame(2, substr_count($content, '1 KPI(s)'));
        $this->assertStringContainsString('Performance', $content);
        $this->assertStringContainsString('Compliance', $content);
    }

    #[Test]
    public function it_shows_uncategorized_group_when_kpi_has_no_mapped_categories()
    {
        $admin = $this->loginAsAdmin();

        $this->createKpi($admin->id, 'No Category KPI', 'NO_CATEGORY_KPI', 'completion', 100);

        $response = $this->get(route('admin.kpis.index'));
        $response->assertOk();

        $content = (string) $response->getContent();
        $this->assertStringContainsString('Uncategorized', $content);
        $this->assertStringContainsString('No Category KPI', $content);
    }

    private function createKpi(int $userId, string $name, string $code, string $type, float $weight): Kpi
    {
        return Kpi::query()->create([
            'name' => $name,
            'code' => $code,
            'type' => $type,
            'description' => $name . ' description.',
            'weight' => $weight,
            'is_active' => true,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }
}
