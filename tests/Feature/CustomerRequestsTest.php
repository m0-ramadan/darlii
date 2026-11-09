<?php

namespace Tests\Feature;

use App\Models\CustomerRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function agent(): User
    {
        return User::where('email', 'agent1@darlii.eg')->firstOrFail();
    }

    private function otherAgent(): User
    {
        return User::where('email', 'agent2@darlii.eg')->firstOrFail();
    }

    private function location(): Location
    {
        return Location::whereIn('type', ['city', 'district'])->where('status', true)->firstOrFail();
    }

    public function test_all_dashboard_accounts_can_open_and_see_customer_requests(): void
    {
        $request = CustomerRequest::create([
            'customer_name' => 'عميل تجريبي', 'phone' => '01000000000', 'need' => 'شقة 3 غرف',
            'location_id' => $this->location()->id, 'status' => 'new', 'created_by' => $this->agent()->id,
        ]);

        $this->actingAs($this->agent())->get('/dashboard/customer-requests')->assertOk()->assertSee('عميل تجريبي');
        $this->actingAs($this->otherAgent())->get('/dashboard/customer-requests')->assertOk()->assertDontSee('عميل تجريبي');
        $this->actingAs($this->agent())->get('/dashboard/customer-requests/' . $request->id . '/edit')->assertOk();
        $this->actingAs($this->otherAgent())->get('/dashboard/customer-requests/' . $request->id . '/edit')->assertForbidden();
        $this->actingAs($this->agent())->get('/dashboard/customer-requests/stats')->assertOk();
        $admin = User::where('email', 'admin@darlii.eg')->firstOrFail();
        $this->actingAs($admin)->get('/admin/customer-requests')->assertOk()->assertSee('عميل تجريبي');
    }

    public function test_customer_request_can_be_created_and_status_updated(): void
    {
        $agent = $this->agent();
        $location = $this->location();

        $this->actingAs($agent)->post('/dashboard/customer-requests', [
            'customer_name' => 'أحمد محمد',
            'phone' => '01123456789',
            'need' => 'فيلا بحديقة في بني سويف',
            'location_id' => $location->id,
            'address_details' => 'شارع النيل',
            'notes' => 'يفضل التواصل مساءً',
        ])->assertRedirect(route('dashboard.customer-requests.index'));

        $request = CustomerRequest::firstOrFail();
        $this->assertSame('new', $request->status);

        $this->actingAs($this->otherAgent())->patch('/dashboard/customer-requests/' . $request->id . '/status', [
            'status' => 'contacted',
        ])->assertForbidden();

        $this->actingAs($this->agent())->patch('/dashboard/customer-requests/' . $request->id . '/status', [
            'status' => 'contacted',
        ])->assertRedirect();

        $this->assertSame('contacted', $request->fresh()->status);
    }

    public function test_address_must_be_a_valid_location(): void
    {
        $this->actingAs($this->agent())->from('/dashboard/customer-requests/create')->post('/dashboard/customer-requests', [
            'customer_name' => 'عميل', 'phone' => '01000000000', 'need' => 'احتياج',
            'location_id' => 999999,
        ])->assertSessionHasErrors('location_id');

        $this->assertSame(0, CustomerRequest::count());
    }


    public function test_admin_panel_requests_section_is_available_to_super_admin_and_manager(): void
    {
        $super = User::where('email', 'super@darlii.eg')->firstOrFail();
        $admin = User::where('email', 'admin@darlii.eg')->firstOrFail();
        $manager = User::where('email', 'manager@darlii.eg')->firstOrFail();

        foreach ([$super, $admin, $manager] as $user) {
            $this->actingAs($user)->get('/admin/customer-requests')->assertOk();
            $this->actingAs($user)->get('/admin/customer-requests/create')->assertOk();
            $this->actingAs($user)->get('/admin/customer-requests/stats')->assertOk();
        }

        $this->actingAs($manager)->post('/admin/customer-requests', [
            'customer_name' => 'عميل من الأدمن',
            'phone' => '01234567890',
            'need' => 'مكتب إداري',
            'location_id' => $this->location()->id,
        ])->assertRedirect(route('admin.customer-requests.index'));

        $this->assertDatabaseHas('customer_requests', ['customer_name' => 'عميل من الأدمن', 'created_by' => $manager->id]);
    }

    public function test_each_manager_only_sees_own_requests_while_admin_sees_all(): void
    {
        $admin = User::where('email', 'admin@darlii.eg')->firstOrFail();
        $managerRole = \App\Models\Role::where('slug', 'manager')->firstOrFail();

        $managerB = User::create([
            'name' => 'مسؤول ثاني',
            'email' => 'manager2@example.com',
            'password' => 'Password@123',
            'status' => 'active',
            'manager_id' => $admin->id,
        ]);
        $managerB->roles()->attach($managerRole->id);

        $managerA = User::where('email', 'manager@darlii.eg')->firstOrFail();

        $this->actingAs($managerA)->post('/admin/customer-requests', [
            'customer_name' => 'طلب خاص بالمسؤول أ',
            'phone' => '01011112222',
            'need' => 'شقة',
            'location_id' => $this->location()->id,
        ])->assertRedirect(route('admin.customer-requests.index'));

        $request = CustomerRequest::where('customer_name', 'طلب خاص بالمسؤول أ')->firstOrFail();

        $this->actingAs($managerA)->get('/admin/customer-requests')->assertOk()->assertSee('طلب خاص بالمسؤول أ');
        $this->actingAs($managerB)->get('/admin/customer-requests')->assertOk()->assertDontSee('طلب خاص بالمسؤول أ');
        $this->actingAs($managerB)->get('/admin/customer-requests/' . $request->id . '/edit')->assertForbidden();
        $this->actingAs($managerB)->patch('/admin/customer-requests/' . $request->id . '/status', ['status' => 'contacted'])->assertForbidden();
        $this->actingAs($admin)->get('/admin/customer-requests')->assertOk()->assertSee('طلب خاص بالمسؤول أ');
    }

    public function test_only_creator_or_admin_can_delete_request(): void
    {
        $request = CustomerRequest::create([
            'customer_name' => 'عميل', 'phone' => '01000000000', 'need' => 'احتياج',
            'location_id' => $this->location()->id, 'status' => 'new', 'created_by' => $this->agent()->id,
        ]);

        $this->actingAs($this->otherAgent())->delete('/dashboard/customer-requests/' . $request->id)->assertForbidden();
        $this->actingAs($this->agent())->delete('/dashboard/customer-requests/' . $request->id)->assertRedirect();
        $this->assertDatabaseMissing('customer_requests', ['id' => $request->id]);
    }
}
