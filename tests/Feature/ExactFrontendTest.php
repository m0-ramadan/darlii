<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExactFrontendTest extends TestCase
{
    use RefreshDatabase;
    public function test_english_home_uses_exact_darli_frontend(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('themes/homzen/css/darli-home.css', false)
            ->assertSee('Discover the Best Properties', false)
            ->assertSee('ae-anno-announcement-wrapper', false)
            ->assertSee('vendor/core/plugins/announcement/js/announcement.js', false);
    }

    public function test_arabic_home_keeps_darli_rtl_frontend(): void
    {
        $this->get('/ar')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('/themes/homzen/images/logo/darli-logo-ar.png', false)
            ->assertDontSee('/themes/homzen/themes/homzen/', false)
            ->assertSee('themes/homzen/css/darli-home.css', false)
            ->assertSee('اكتشف أفضل العقارات', false)
            ->assertSee('ae-anno-announcement-wrapper', false)
            ->assertSee('vendor/core/plugins/announcement/js/announcement.js', false);
    }

    public function test_announcement_bar_rendered_with_content(): void
    {
        $this->get('/ar')
            ->assertOk()
            ->assertSee('ae-anno-announcement__controls', false)
            ->assertSee('ae-anno-announcement__text', false)
            ->assertSee('استفد من العروض لفترة محدودة على المنازل الفاخرة', false);

        $this->get('/en')
            ->assertOk()
            ->assertSee('ae-anno-announcement__controls', false)
            ->assertSee('ae-anno-announcement__text', false)
            ->assertSee('Discover Your Dream Home with Our Latest Listings', false);
    }

    public function test_property_fallback_placeholder_is_present_and_configured(): void
    {
        $this->assertFileExists(public_path('themes/homzen/images/darli-placeholder.png'));
        $this->assertGreaterThan(100000, filesize(public_path('themes/homzen/images/darli-placeholder.png')));

        $this->assertFileExists(public_path('themes/homzen/images/darli-placeholder-en.png'));
        $this->assertGreaterThan(100000, filesize(public_path('themes/homzen/images/darli-placeholder-en.png')));

        $this->assertFileExists(public_path('storage/general/placeholder.png'));
        $this->assertGreaterThan(100000, filesize(public_path('storage/general/placeholder.png')));

        $this->assertFileExists(public_path('storage/general/placeholder-en.png'));
        $this->assertGreaterThan(100000, filesize(public_path('storage/general/placeholder-en.png')));

        $property = new \App\Models\Property();
        $this->assertStringContainsString('darli-placeholder.png', $property->placeholderUrl('ar'));
        $this->assertStringContainsString('darli-placeholder-en.png', $property->placeholderUrl('en'));
    }

    public function test_original_property_detail_is_served(): void
    {
        $this->get('/en/properties/private-estate-magnificent-views')
            ->assertOk()
            ->assertSee('Private Estate Magnificent Views', false)
            ->assertSee('themes/homzen/css/style.css?v=1.3.3', false);
    }
}
