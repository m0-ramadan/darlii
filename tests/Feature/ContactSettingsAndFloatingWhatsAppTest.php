<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AdminUsersSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSettingsAndFloatingWhatsAppTest extends TestCase
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

    public function test_public_pages_render_floating_whatsapp_button_with_default_number(): void
    {
        $responseHome = $this->get('/ar');
        $responseHome->assertOk();
        $responseHome->assertSee('darli-floating-wa', false);
        $responseHome->assertSee('https://wa.me/201034905812', false);
        $responseHome->assertSee('تواصل معنا عبر واتساب', false);

        $responseProps = $this->get('/ar/properties');
        $responseProps->assertOk();
        $responseProps->assertSee('darli-floating-wa', false);
        $responseProps->assertSee('https://wa.me/201034905812', false);
    }

    public function test_public_footer_renders_dynamic_social_media_and_phone(): void
    {
        $response = $this->get('/ar');
        $response->assertOk();
        $response->assertSee('+20 10 34905812', false);
        $response->assertSee('facebook.com', false);
        $response->assertSee('instagram.com', false);
    }

    public function test_admin_can_update_contact_and_social_settings_and_changes_reflect_on_public_site(): void
    {
        $this->actingAs($this->admin);

        // Access contact settings page in dashboard
        $responsePage = $this->get('/admin/settings/contact');
        $responsePage->assertOk();
        $responsePage->assertSee('بيانات التواصل', false);
        $responsePage->assertSee('+20 10 34905812', false);

        // Update contact phone, WhatsApp and Facebook via dashboard PUT
        $responseUpdate = $this->put('/admin/settings/contact', [
            'contact_phone' => '+20 10 99887766',
            'contact_whatsapp' => '+20 10 99887766',
            'contact_email' => 'support@darlii.eg',
            'contact_address' => 'ميدان المديرية، بني سويف',
            'contact_facebook' => 'https://facebook.com/darliicustom',
            'contact_twitter' => 'https://x.com/darliicustom',
            'contact_instagram' => 'https://instagram.com/darliicustom',
            'contact_linkedin' => 'https://linkedin.com/company/darliicustom',
            'contact_youtube' => 'https://youtube.com/@darliicustom',
            'contact_tiktok' => 'https://tiktok.com/@darliicustom',
        ]);

        $responseUpdate->assertRedirect();
        $responseUpdate->assertSessionHas('success');

        // Verify database and cache updated
        $this->assertSame('+20 10 99887766', setting('contact.phone'));
        $this->assertSame('+20 10 99887766', setting('contact.whatsapp'));
        $this->assertSame('https://facebook.com/darliicustom', setting('contact.facebook'));

        // Public site should reflect the updated phone, WhatsApp, and social links
        $responsePublic = $this->get('/ar');
        $responsePublic->assertOk();
        $responsePublic->assertSee('https://wa.me/201099887766', false);
        $responsePublic->assertSee('+20 10 99887766', false);
        $responsePublic->assertSee('https://facebook.com/darliicustom', false);
        $responsePublic->assertSee('https://tiktok.com/@darliicustom', false);
    }
}
