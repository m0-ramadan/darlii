<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\Seo\SitemapGenerator;
use App\Services\Settings\SettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformContentSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        app(SettingsRepository::class)->flush();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_home_title_and_description_come_from_dashboard_seo_settings(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>دارلي للتسويق العقاري ببني سويف</title>', false)
            ->assertSee('أكبر منصة للتسويق العقاري في محافظة بني سويف', false);
    }

    public function test_home_seo_can_be_changed_from_dashboard_settings(): void
    {
        Setting::query()->where('key', 'seo.title_ar')->update(['value' => 'عنوان تجربة من الداشبورد']);
        app(SettingsRepository::class)->flush();

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>عنوان تجربة من الداشبورد</title>', false);
    }

    public function test_contact_page_has_no_headquarters_address_or_map(): void
    {
        $this->get('/contact-us')
            ->assertOk()
            ->assertDontSee('المقر الرئيسي', false)
            ->assertDontSee('خريطة بني سويف', false)
            ->assertDontSee('google.com/maps/embed', false);
    }

    public function test_footer_has_no_empty_social_icons_or_address(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('صلاح سالم', false)
            ->assertDontSee('aria-label="YouTube"', false)
            ->assertDontSee('aria-label="LinkedIn"', false)
            ->assertDontSee('aria-label="X (Twitter)"', false);
    }

    public function test_companies_page_uses_darlii_contact_numbers(): void
    {
        $this->get('/agents')
            ->assertOk()
            ->assertSee('201034905812', false)
            ->assertSee('فريق دارلي', false)
            ->assertDontSee('+201000000004', false)
            ->assertDontSee('+201000000005', false);
    }

    public function test_robots_points_to_the_current_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: ' . rtrim(config('app.url'), '/') . '/sitemap.xml', false);
    }

    public function test_sitemap_generator_includes_public_content(): void
    {
        $urls = app(SitemapGenerator::class)->urls();
        $locations = array_column($urls, 'loc');

        $this->assertGreaterThan(50, count($urls));
        $this->assertContains(url('/properties'), $locations);
        $this->assertContains(url('/projects'), $locations);
        $this->assertContains(url('/blog'), $locations);

        $property = \App\Models\Property::query()
            ->with('translations')
            ->whereIn('status', ['published', 'sold'])
            ->first();

        if ($property && ($slug = $property->translations->firstWhere('locale', 'ar')?->slug)) {
            $this->assertContains(url('/properties/' . $slug), $locations);
        }
    }

    public function test_admin_seo_page_has_sitemap_controls(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/settings/seo')
            ->assertOk()
            ->assertSee('خريطة الموقع', false)
            ->assertSee('تحديث خريطة الموقع الآن', false);
    }

    public function test_sitemap_file_is_valid_xml(): void
    {
        app(SitemapGenerator::class)->write();

        $path = public_path('sitemap.xml');
        $this->assertFileExists($path);

        $xml = simplexml_load_file($path);
        $this->assertNotFalse($xml);
        $this->assertSame('urlset', $xml->getName());
        $this->assertGreaterThan(50, $xml->count());
    }
}
