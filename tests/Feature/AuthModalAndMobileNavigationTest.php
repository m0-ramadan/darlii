<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthModalAndMobileNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_public_pages_render_auth_modals_and_mobile_assets(): void
    {
        $response = $this->get('/ar');
        $response->assertOk()
            ->assertSee('id="modalLogin"', false)
            ->assertSee('id="modalRegister"', false)
            ->assertSee('darli-auth-modal.js', false)
            ->assertSee('mobile-nav-toggler', false)
            ->assertSee('mobile-menu', false)
            ->assertDontSee('linkedin-openid', false);

        $propertiesResponse = $this->get('/ar/properties');
        $propertiesResponse->assertOk()
            ->assertSee('id="modalLogin"', false)
            ->assertSee('id="modalRegister"', false)
            ->assertSee('darli-auth-modal.js', false);
    }

    public function test_social_login_restricted_to_google_and_facebook(): void
    {
        $this->get('/ar/auth/google')->assertRedirect();
        $this->get('/ar/auth/facebook')->assertRedirect();

        // LinkedIn, GitHub, OpenID should not be matched by the route
        $this->get('/ar/auth/linkedin')->assertNotFound();
        $this->get('/ar/auth/github')->assertNotFound();
        $this->get('/ar/auth/linkedin-openid')->assertNotFound();
    }

    public function test_ajax_login_failure_returns_json_error(): void
    {
        $response = $this->postJson('/ar/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_ajax_login_success_returns_redirect(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/ar/login', [
            'email' => 'buyer@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure(['message', 'redirect', 'user']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_ajax_register_creates_account_and_logs_in(): void
    {
        $response = $this->postJson('/ar/register', [
            'first_name' => 'أحمد',
            'last_name' => 'علي',
            'email' => 'ahmed.ali@example.com',
            'phone' => '01012345678',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure(['message', 'redirect', 'user']);

        $this->assertDatabaseHas('users', [
            'email' => 'ahmed.ali@example.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_authenticated_user_sees_account_controls_instead_of_guest_modal(): void
    {
        $user = User::factory()->create(['name' => 'محمد خالد']);

        $response = $this->actingAs($user)->get('/ar');
        $response->assertOk()
            ->assertSee('لوحة التحكم', false)
            ->assertSee('/logout', false);
    }
}
