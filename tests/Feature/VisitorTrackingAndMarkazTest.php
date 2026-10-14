<?php

namespace Tests\Feature;

use App\Models\SiteVisitor;
use App\Models\User;
use Database\Seeders\AdminUsersSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorTrackingAndMarkazTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CoreSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUsersSeeder::class);
        $this->seed(SettingsSeeder::class);
        app(\App\Services\Settings\SettingsRepository::class)->flush();

        $this->admin = User::where('email', 'admin@darlii.eg')->first();
    }

    public function test_api_tracks_visitor_and_resolves_governorate_and_markaz_from_coordinates(): void
    {
        $visitorId = 'vis_test_markaz_nasser_123';

        // إحداثيات مركز ناصر بمحافظة بني سويف (29.1810, 31.0630)
        $response = $this->postJson('/api/visitors/track', [
            'visitor_id' => $visitorId,
            'url' => 'http://localhost:8001/ar/properties',
            'page_title' => 'عقارات للبيع في بني سويف',
            'screen_resolution' => '390x844',
            'language' => 'ar-EG',
            'latitude' => 29.1810,
            'longitude' => 31.0630,
            'accuracy' => 15,
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'visitor_id' => $visitorId,
            'governorate' => 'بني سويف',
        ]);
        $this->assertStringContainsString('ناصر', $response->json('city_or_markaz'));

        $this->assertDatabaseHas('site_visitors', [
            'visitor_id' => $visitorId,
            'governorate' => 'بني سويف',
            'device_type' => 'mobile',
            'device_brand' => 'Apple',
            'device_model' => 'iPhone',
        ]);

        $this->assertDatabaseHas('site_visitor_hits', [
            'url' => 'http://localhost:8001/ar/properties',
            'page_title' => 'عقارات للبيع في بني سويف',
        ]);
    }

    public function test_api_updates_gps_location_and_updates_markaz(): void
    {
        $visitor = SiteVisitor::create([
            'visitor_id' => 'vis_gps_update_789',
            'ip_address' => '127.0.0.1',
            'governorate' => 'بني سويف',
            'city_or_markaz' => 'مدينة بني سويف',
            'device_type' => 'desktop',
            'visits_count' => 1,
            'page_views_count' => 1,
        ]);

        // إحداثيات التجمع الخامس بالقاهرة (30.0131, 31.4913)
        $response = $this->postJson('/api/visitors/location', [
            'visitor_id' => 'vis_gps_update_789',
            'latitude' => 30.0131,
            'longitude' => 31.4913,
            'accuracy' => 10,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'governorate' => 'القاهرة',
        ]);
        $this->assertNotEmpty($response->json('city_or_markaz'));

        $visitor->refresh();
        $this->assertEquals('القاهرة', $visitor->governorate);
        $this->assertEquals('gps', $visitor->location_source);
    }

    public function test_admin_can_access_visitors_index_and_view_statistics(): void
    {
        SiteVisitor::create([
            'visitor_id' => 'vis_index_test_1',
            'ip_address' => '196.221.15.20',
            'governorate' => 'بني سويف',
            'city_or_markaz' => 'مركز الواسطى',
            'device_type' => 'mobile',
            'browser' => 'Google Chrome',
            'os' => 'Android 14',
            'visits_count' => 3,
            'page_views_count' => 7,
            'last_active_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/visitors');

        $response->assertStatus(200);
        $response->assertSee('سجل الزوار والمواقع الجغرافية');
        $response->assertSee('مركز الواسطى');
        $response->assertSee('بني سويف');
        $response->assertSee('196.221.15.20');
        $response->assertSee('Google Chrome');
    }

    public function test_admin_can_filter_visitors_by_governorate_and_device(): void
    {
        SiteVisitor::create([
            'visitor_id' => 'vis_filter_alex',
            'ip_address' => '197.35.10.15',
            'governorate' => 'الإسكندرية',
            'city_or_markaz' => 'حي المنتزه',
            'device_type' => 'desktop',
            'last_active_at' => now(),
        ]);

        SiteVisitor::create([
            'visitor_id' => 'vis_filter_cairo',
            'ip_address' => '197.35.10.16',
            'governorate' => 'القاهرة',
            'city_or_markaz' => 'مدينة نصر',
            'device_type' => 'mobile',
            'last_active_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/visitors?governorate=الإسكندرية');
        $response->assertStatus(200);
        $response->assertSee('حي المنتزه');
        $response->assertDontSee('مدينة نصر');
    }

    public function test_admin_can_view_visitor_details_and_delete_record(): void
    {
        $visitor = SiteVisitor::create([
            'visitor_id' => 'vis_show_delete_test',
            'ip_address' => '41.233.10.50',
            'governorate' => 'بني سويف',
            'city_or_markaz' => 'مركز ببا',
            'district' => 'قرية طنسا بني مالو',
            'device_type' => 'mobile',
            'browser' => 'Apple Safari',
            'os' => 'iOS 17.2',
            'latitude' => 28.9210,
            'longitude' => 30.9850,
            'last_active_at' => now(),
        ]);

        $showResponse = $this->actingAs($this->admin)->get("/admin/visitors/{$visitor->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('مركز ببا');
        $showResponse->assertSee('قرية طنسا بني مالو');
        $showResponse->assertSee('Apple Safari');

        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/visitors/{$visitor->id}");
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('site_visitors', ['id' => $visitor->id]);
    }

    public function test_public_pages_render_visitor_tracker_script(): void
    {
        $response = $this->get('/ar');
        $response->assertStatus(200);
        $response->assertSee('darli-visitor-tracker.js');
    }
}
