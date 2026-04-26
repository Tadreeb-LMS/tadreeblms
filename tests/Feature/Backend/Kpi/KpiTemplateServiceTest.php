<?php

namespace Tests\Feature\Backend\Kpi;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Kpi;
use App\Models\KpiTemplate;
use App\Models\KpiTemplateItem;
use App\Services\Kpi\KpiTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_applies_template_and_creates_kpis()
    {
        // Create a test template
        $template = KpiTemplate::create([
            'name' => 'Test Suite',
            'slug' => 'test-suite',
            'category' => 'testing',
            'description' => 'Test template',
        ]);

        KpiTemplateItem::create([
            'template_id' => $template->id,
            'name' => 'Metric A',
            'code' => 'METRIC_A',
            'description' => 'First metric for testing',
            'type' => 'percentage',
            'weight' => 50,
            'is_active' => true,
        ]);

        KpiTemplateItem::create([
            'template_id' => $template->id,
            'name' => 'Metric B',
            'code' => 'METRIC_B',
            'description' => 'Second metric for testing',
            'type' => 'numeric',
            'weight' => 50,
            'is_active' => true,
        ]);

        $service = app(KpiTemplateService::class);
        $result = $service->applyTemplate($template);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['created_count']);
        $this->assertDatabaseHas('kpis', ['code' => 'METRIC_A']);
        $this->assertDatabaseHas('kpis', ['code' => 'METRIC_B']);
    }

    public function test_service_skips_existing_kpis()
    {
        Kpi::create([
            'name' => 'Existing KPI',
            'code' => 'EXISTING_KPI',
            'description' => 'An existing KPI',
            'type' => 'percentage',
            'weight' => 30,
            'is_active' => true,
        ]);

        $template = KpiTemplate::create([
            'name' => 'New Suite',
            'slug' => 'new-suite',
            'category' => 'testing',
        ]);

        KpiTemplateItem::create([
            'template_id' => $template->id,
            'name' => 'Existing KPI',
            'code' => 'EXISTING_KPI',
            'description' => 'Existing description',
            'type' => 'percentage',
            'weight' => 50,
        ]);

        KpiTemplateItem::create([
            'template_id' => $template->id,
            'name' => 'New KPI',
            'code' => 'NEW_KPI',
            'description' => 'A new KPI',
            'type' => 'numeric',
            'weight' => 50,
        ]);

        $service = app(KpiTemplateService::class);
        $result = $service->applyTemplate($template);

        $this->assertEquals(1, $result['created_count']);
        $this->assertEquals(1, $result['skipped_count']);
        $this->assertDatabaseHas('kpis', ['code' => 'NEW_KPI']);
    }

    public function test_service_generates_template_preview()
    {
        $template = KpiTemplate::create([
            'name' => 'Preview Template',
            'slug' => 'preview-template',
            'category' => 'testing',
            'description' => 'For preview testing',
        ]);

        KpiTemplateItem::create([
            'template_id' => $template->id,
            'name' => 'Item 1',
            'code' => 'ITEM_1',
            'description' => 'First item',
            'type' => 'percentage',
            'weight' => 50,
        ]);

        KpiTemplateItem::create([
            'template_id' => $template->id,
            'name' => 'Item 2',
            'code' => 'ITEM_2',
            'description' => 'Second item',
            'type' => 'percentage',
            'weight' => 50,
        ]);

        $service = app(KpiTemplateService::class);
        $preview = $service->previewTemplate($template);

        $this->assertEquals(2, count($preview['items']));
        $this->assertEquals($template->name, $preview['template']['name']);
        $this->assertEquals(2, $preview['template']['item_count']);
    }

    public function test_service_validates_template()
    {
        $template = KpiTemplate::create([
            'name' => 'Valid Template',
            'slug' => 'valid-template',
            'category' => 'testing',
        ]);

        KpiTemplateItem::create([
            'template_id' => $template->id,
            'name' => 'Item A',
            'code' => 'ITEM_A',
            'description' => 'Item A description',
            'type' => 'numeric',
            'weight' => 50,
        ]);

        $service = app(KpiTemplateService::class);
        $validation = $service->validateTemplate($template);

        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    public function test_service_calculates_template_stats()
    {
        $template = KpiTemplate::create([
            'name' => 'Stats Template',
            'slug' => 'stats-template',
            'category' => 'testing',
        ]);

        KpiTemplateItem::create([
            'template_id' => $template->id,
            'name' => 'Item 1',
            'code' => 'STATS_1',
            'description' => 'Stats item 1',
            'type' => 'percentage',
            'weight' => 30,
        ]);

        KpiTemplateItem::create([
            'template_id' => $template->id,
            'name' => 'Item 2',
            'code' => 'STATS_2',
            'description' => 'Stats item 2',
            'type' => 'percentage',
            'weight' => 70,
        ]);

        $service = app(KpiTemplateService::class);
        $stats = $service->getTemplateStats($template);

        $this->assertEquals(2, $stats['total_items']);
        $this->assertEquals(100, $stats['total_weight']);
        $this->assertEquals(50, $stats['average_weight']);
    }
}
