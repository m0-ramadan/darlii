<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Project;
use App\Support\BeniSuefCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsPageAndLocationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_projects_page_renders_hero_filter_and_cards(): void
    {
        $response = $this->get('/ar/projects');

        $response->assertStatus(200);
        $response->assertSee('المشروعات العقارية');
        $response->assertSee('أكتشف أفضل الفرص الإستثمارية');
        $response->assertSee('ابحث عن مشروع');
        $response->assertSee('كل المناطق');
        $response->assertSee('دارلي');
    }

    public function test_projects_page_filters_by_search_query(): void
    {
        $location = Location::first();
        $project = Project::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference_code' => 'PRJ-' . uniqid(),
            'status' => 'published',
            'location_id' => $location?->id,
            'published_at' => now(),
            'blocks_count' => 12,
            'flats_count' => 648,
            'floors_count' => 9,
            'investor_name' => 'شركة الاختبار العقارية',
        ]);
        $project->translations()->create([
            'locale' => 'ar',
            'name' => 'مشروع تجريبي مميز',
            'slug' => 'test-project-unique',
        ]);

        $response = $this->get('/ar/projects?q=' . urlencode('مشروع تجريبي مميز'));
        $response->assertStatus(200);
        $response->assertSee('مشروع تجريبي مميز');
    }

    public function test_projects_page_filters_by_location_id(): void
    {
        $location = Location::first();
        $project = Project::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference_code' => 'PRJ-' . uniqid(),
            'status' => 'published',
            'location_id' => $location->id,
            'published_at' => now(),
            'blocks_count' => 12,
            'flats_count' => 648,
            'floors_count' => 9,
            'investor_name' => 'شركة الاختبار العقارية',
        ]);
        $project->translations()->create([
            'locale' => 'ar',
            'name' => 'مشروع في موقع محدد',
            'slug' => 'test-project-location',
        ]);

        $response = $this->get('/ar/projects?location_id=' . $location->id);
        $response->assertStatus(200);
        $response->assertSee('مشروع في موقع محدد');
    }

    public function test_beni_suef_catalog_has_all_markazes_and_districts(): void
    {
        $cities = BeniSuefCatalog::cities();
        $districts = BeniSuefCatalog::districts();

        $this->assertGreaterThanOrEqual(8, count($cities));
        $this->assertGreaterThanOrEqual(50, count($districts));

        // Check each markaz exists
        $cityKeys = array_column($cities, 'key');
        $this->assertContains('beni-suef-city', $cityKeys);
        $this->assertContains('new-beni-suef', $cityKeys);
        $this->assertContains('el-wasta', $cityKeys);
        $this->assertContains('nasser', $cityKeys);
        $this->assertContains('ihnasya', $cityKeys);
        $this->assertContains('biba', $cityKeys);
        $this->assertContains('sumusta', $cityKeys);
        $this->assertContains('fashn', $cityKeys);
    }
}
