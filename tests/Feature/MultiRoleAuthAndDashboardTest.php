<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\AgentProfile;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Project;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiRoleAuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->withoutMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class]);

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\SettingsSeeder::class);
    }

    public function test_client_can_register_with_name_and_phone_only(): void
    {
        $response = $this->postJson('/ar/register', [
            'account_type' => 'client',
            'name' => 'عميل تجريبي',
            'phone' => '01012345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'عميل تجريبي',
            'phone' => '01012345678',
            'status' => 'active',
        ]);

        $user = User::where('phone', '01012345678')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('customer'));
    }

    public function test_company_registers_with_pending_status_and_agency_created(): void
    {
        $response = $this->postJson('/ar/register', [
            'account_type' => 'company',
            'name' => 'م. أحمد مدير الشركة',
            'phone' => '01122334455',
            'company_name' => 'شركة النيل للتطوير العقاري',
            'company_email' => 'contact@elnile-develop.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'user' => [
                    'status' => 'pending',
                    'is_pending' => true,
                ],
            ]);

        $user = User::where('email', 'contact@elnile-develop.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pending', $user->status);
        $this->assertTrue($user->hasRole('agency-manager'));

        $this->assertDatabaseHas('agencies', [
            'name' => 'شركة النيل للتطوير العقاري',
            'email' => 'contact@elnile-develop.com',
            'status' => 'pending',
        ]);

        $agency = Agency::where('email', 'contact@elnile-develop.com')->first();
        $this->assertNotNull($agency);
        $this->assertTrue($agency->users->contains($user->id));
    }

    public function test_broker_registers_with_national_id_card_and_pending_status(): void
    {
        $fakeFile = UploadedFile::fake()->create('national_id.jpg', 500, 'image/jpeg');

        $response = $this->post('/ar/register', [
            'account_type' => 'broker',
            'name' => 'سمسار بني سويف',
            'phone' => '01299887766',
            'national_id_image' => $fakeFile,
            'password' => 'brokerpass123',
            'password_confirmation' => 'brokerpass123',
        ]);

        $user = User::where('phone', '01299887766')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pending', $user->status);
        $this->assertTrue($user->hasRole('agent'));

        $profile = AgentProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('pending', $profile->status);
        $this->assertNotEmpty($profile->contract_terms);
        Storage::disk('public')->assertExists($profile->contract_terms);
    }

    public function test_user_can_login_with_phone_number_or_email(): void
    {
        $user = User::create([
            'name' => 'مستخدم الهاتف',
            'phone' => '01055566677',
            'email' => 'phone_user@darlii.eg',
            'password' => bcrypt('mypassword123'),
            'status' => 'active',
        ]);

        // Login using phone
        $response = $this->postJson('/ar/login', [
            'login' => '01055566677',
            'password' => 'mypassword123',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);
        $this->assertAuthenticatedAs($user);

        auth()->logout();

        // Login using email
        $responseEmail = $this->postJson('/ar/login', [
            'login' => 'phone_user@darlii.eg',
            'password' => 'mypassword123',
        ]);

        $responseEmail->assertOk()
            ->assertJson(['success' => true]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_pending_broker_or_company_is_blocked_from_creating_properties(): void
    {
        $pendingBroker = User::create([
            'name' => 'سمسار معلق',
            'phone' => '01099988877',
            'email' => 'pending_broker@darlii.eg',
            'password' => bcrypt('pass12345'),
            'status' => 'pending',
        ]);
        $role = Role::where('slug', 'agent')->first();
        $pendingBroker->roles()->attach($role);

        $response = $this->actingAs($pendingBroker)->get('/ar/dashboard/properties/create');
        $response->assertRedirect('/ar/dashboard');
        $response->assertSessionHas('status_warning');

        $responseStore = $this->actingAs($pendingBroker)->post('/ar/dashboard/properties', [
            'name' => 'شقة تمليك',
            'price' => 500000,
        ]);
        $responseStore->assertRedirect('/ar/dashboard');
        $responseStore->assertSessionHas('status_warning');
    }

    public function test_partner_dashboard_shows_views_count_and_stats(): void
    {
        $broker = User::create([
            'name' => 'سمسار معتمد',
            'phone' => '01011122233',
            'email' => 'active_broker@darlii.eg',
            'password' => bcrypt('pass12345'),
            'status' => 'active',
        ]);
        $role = Role::where('slug', 'agent')->first();
        $broker->roles()->attach($role);

        $currency = Currency::firstOrCreate(['code' => 'EGP'], ['name' => 'Egyptian Pound', 'symbol' => 'EGP', 'is_active' => true]);

        // Create 2 properties for this broker with views
        $p1 = Property::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference_code' => 'TEST-001',
            'owner_id' => $broker->id,
            'currency_id' => $currency->id,
            'price' => 1200000,
            'status' => 'published',
            'views_count' => 150,
        ]);
        $p2 = Property::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference_code' => 'TEST-002',
            'owner_id' => $broker->id,
            'currency_id' => $currency->id,
            'price' => 800000,
            'status' => 'sold',
            'sold_at' => now(),
            'views_count' => 75,
        ]);

        $response = $this->actingAs($broker)->get('/ar/dashboard');
        $response->assertOk();
        $response->assertSee('225'); // sum of views (150 + 75)
        $response->assertSee('TEST-001');
        $response->assertSee('TEST-002');
    }

    public function test_broker_can_mark_property_as_sold(): void
    {
        $broker = User::create([
            'name' => 'سمسار مبيعات',
            'phone' => '01022233344',
            'email' => 'sales_broker@darlii.eg',
            'password' => bcrypt('pass12345'),
            'status' => 'active',
        ]);
        $currency = Currency::firstOrCreate(['code' => 'EGP'], ['name' => 'Egyptian Pound', 'symbol' => 'EGP', 'is_active' => true]);

        $prop = Property::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference_code' => 'SOLD-TEST',
            'owner_id' => $broker->id,
            'currency_id' => $currency->id,
            'price' => 950000,
            'status' => 'published',
        ]);

        $response = $this->actingAs($broker)->post('/ar/dashboard/properties/' . $prop->id . '/sold');
        $response->assertRedirect();

        $prop->refresh();
        $this->assertEquals('sold', $prop->status->value ?? (string) $prop->status);
        $this->assertNotNull($prop->sold_at);
    }

    public function test_admin_can_activate_pending_account(): void
    {
        $admin = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin_test@darlii.eg',
            'password' => bcrypt('admin123'),
            'status' => 'active',
        ]);
        $adminRole = Role::where('slug', 'admin')->first();
        $admin->roles()->attach($adminRole);

        $pendingUser = User::create([
            'name' => 'سمسار قيد الاعتماد',
            'phone' => '01077788899',
            'email' => 'pending_agent@darlii.eg',
            'password' => bcrypt('pass123'),
            'status' => 'pending',
        ]);
        $agentRole = Role::where('slug', 'agent')->first();
        $pendingUser->roles()->attach($agentRole);

        $agentProfile = AgentProfile::create([
            'user_id' => $pendingUser->id,
            'slug' => 'pending-agent-test',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post('/admin/users/' . $pendingUser->id . '/activate');
        $response->assertRedirect();

        $pendingUser->refresh();
        $agentProfile->refresh();

        $this->assertEquals('active', $pendingUser->status);
        $this->assertEquals('active', $agentProfile->status);
    }

    public function test_property_details_contact_respects_platform_only_setting(): void
    {
        setting(['properties.contact_display_mode' => 'platform_only']);
        setting(['contact.phone' => '01000000000']);
        setting(['contact.whatsapp' => '01000000000']);

        $owner = User::create([
            'name' => 'سمسار خاص لا يظهر رقمه',
            'phone' => '01999999999',
            'email' => 'secret_agent@darlii.eg',
            'password' => bcrypt('pass123'),
            'status' => 'active',
        ]);

        $currency = Currency::firstOrCreate(['code' => 'EGP'], ['name' => 'Egyptian Pound', 'symbol' => 'EGP', 'is_active' => true]);

        $property = Property::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference_code' => 'CONTACT-MODE-01',
            'owner_id' => $owner->id,
            'currency_id' => $currency->id,
            'price' => 750000,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $property->translations()->create([
            'locale' => 'ar',
            'name' => 'شقة تمليك للاختبار',
            'slug' => 'test-contact-mode-property',
        ]);

        $response = $this->get('/ar/properties/test-contact-mode-property');
        $response->assertOk();

        // Platform phone must be displayed
        $response->assertSee('01000000000');
        // Private agent phone must NOT be displayed
        $response->assertDontSee('01999999999');
        // Platform title displayed
        $response->assertSee('إدارة منصة دارلي');
    }
}
