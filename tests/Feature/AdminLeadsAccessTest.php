<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeadsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_leads_section_is_primary_super_admin_only(): void
    {
        $super = User::where('email', 'super@darlii.eg')->firstOrFail();
        $admin = User::where('email', 'admin@darlii.eg')->firstOrFail();
        $manager = User::where('email', 'manager@darlii.eg')->firstOrFail();

        $this->actingAs($super)->get('/admin/leads')->assertOk();
        $this->actingAs($admin)->get('/admin/leads')->assertForbidden();
        $this->actingAs($manager)->get('/admin/leads')->assertForbidden();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertDontSee('العملاء المحتملون');
        $this->actingAs($super)->get('/admin')->assertOk()->assertSee('العملاء المحتملون');
    }
}
