<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\AgentProfile;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUsersSeeder;
use Database\Seeders\BeniSuefAgenciesSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentsPageAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CoreSeeder::class,
            RolePermissionSeeder::class,
            AdminUsersSeeder::class,
            BeniSuefAgenciesSeeder::class,
        ]);
    }

    public function test_public_agents_page_renders_with_exact_title_and_seeded_companies(): void
    {
        $response = $this->get('/ar/agents');

        $response->assertOk();
        $response->assertSee('تعرف على نخبة وكلائنا العقاريين', false);
        $response->assertSee('شركة النيل للتطوير والاستثمار العقاري', false);
        $response->assertSee('مجموعة الصعيد للتعمير والاستثمار', false);
        $response->assertSee('معتمد', false);
    }

    public function test_public_agents_search_filter(): void
    {
        $response = $this->get('/ar/agents?q=' . urlencode('النيل'));

        $response->assertOk();
        $response->assertSee('شركة النيل للتطوير والاستثمار العقاري', false);
        $response->assertDontSee('مجموعة الصعيد للتعمير والاستثمار', false);
    }

    public function test_public_agents_location_filter(): void
    {
        $response = $this->get('/ar/agents?location=' . urlencode('الواسطى'));

        $response->assertOk();
        $response->assertSee('شركة الأمل للتطوير العقاري بالواسطى', false);
    }

    public function test_admin_can_view_agents_index(): void
    {
        $admin = User::where('email', 'admin@darlii.eg')->first();

        $response = $this->actingAs($admin)->get('/admin/agents');

        $response->assertOk();
        $response->assertSee('الشركات والوكلاء العقاريين', false);
        $response->assertSee('شركة النيل للتطوير والاستثمار العقاري', false);
    }

    public function test_admin_can_toggle_agent_verification(): void
    {
        $admin = User::where('email', 'admin@darlii.eg')->first();
        $agent = AgentProfile::first();
        $initialState = $agent->isVerified();

        $response = $this->actingAs($admin)->post("/admin/agents/{$agent->id}/verify");

        $response->assertRedirect();
        $this->assertNotEquals($initialState, $agent->fresh()->isVerified());
    }

    public function test_admin_can_toggle_agent_status(): void
    {
        $admin = User::where('email', 'admin@darlii.eg')->first();
        $agent = AgentProfile::first();

        $response = $this->actingAs($admin)->post("/admin/agents/{$agent->id}/status");

        $response->assertRedirect();
        $this->assertEquals('inactive', $agent->fresh()->status);
    }
}

