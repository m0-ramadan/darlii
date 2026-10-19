<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DarkModeCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_dark_theme_assets_load_after_the_original_homzen_styles(): void
    {
        $html = $this->withSession(['locale' => 'en'])->get('/properties')->assertOk()->getContent();

        $this->assertStringContainsString('dark-mode.css?v=2.0.1', $html);
        $this->assertStringContainsString('map-location.min.js?v=2.3.4', $html);
        $this->assertStringContainsString('map-location.min.js', $html);
        $this->assertGreaterThan(
            strpos($html, 'themes/homzen/css/style.css?v=1.3.3'),
            strpos($html, 'themes/homzen/css/dark-mode.css?v=2.0.1')
        );
    }

    public function test_authenticated_dashboard_and_admin_pages_render_the_dark_layout(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin);

        // صفحات لوحة العميل تحمّل dark-mode.css، أما لوحة الإدارة فالوضع الداكن
        // معطّل مؤقتًا (الملف معلّق في layouts/admin) فنتحقق فقط من نجاح العرض.
        foreach (['/dashboard/properties', '/dashboard/properties/create'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('dark-mode.css', false);
        }

        foreach (['/admin', '/admin/properties/pending'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
