<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExactFrontendTest extends TestCase
{
    use RefreshDatabase;
    public function test_english_home_uses_exact_darli_frontend(): void
    {
        $this->withSession(['locale' => 'en'])->get('/')
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('themes/darlii/css/darli-home', false)
            ->assertSee('Discover the Best Properties', false)
            ->assertSee('ae-anno-announcement-wrapper', false)
            ->assertSee('vendor/core/plugins/announcement/js/announcement.js', false);
    }

    public function test_arabic_home_keeps_darli_rtl_frontend(): void
    {
        $this->withSession(['locale' => 'ar'])->get('/')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('/themes/darlii/images/logo/darli-logo-ar.png', false)
            ->assertDontSee('/themes/darlii/themes/darlii/', false)
            ->assertSee('themes/darlii/css/darli-home', false)
            ->assertSee('اكتشف أفضل العقارات', false)
            ->assertSee('ae-anno-announcement-wrapper', false)
            ->assertSee('vendor/core/plugins/announcement/js/announcement.js', false);
    }

    public function test_announcement_bar_rendered_with_content(): void
    {
        $this->withSession(['locale' => 'ar'])->get('/')
            ->assertOk()
            ->assertSee('ae-anno-announcement__controls', false)
            ->assertSee('ae-anno-announcement__text', false)
            ->assertSee('استفد من العروض لفترة محدودة على المنازل الفاخرة', false);

        $this->withSession(['locale' => 'en'])->get('/')
            ->assertOk()
            ->assertSee('ae-anno-announcement__controls', false)
            ->assertSee('ae-anno-announcement__text', false)
            ->assertSee('Discover Your Dream Home with Our Latest Listings', false);
    }

    public function test_property_fallback_placeholder_is_present_and_configured(): void
    {
        // الصور قد تُضغط لاحقًا، لذا نتحقق من صلاحيتها وأبعادها بدل حجم الملف.
        foreach ([
            public_path('themes/darlii/images/darli-placeholder.png'),
            public_path('themes/darlii/images/darli-placeholder-en.png'),
            public_path('storage/general/placeholder.png'),
            public_path('storage/general/placeholder-en.png'),
        ] as $path) {
            $this->assertFileExists($path);
            $info = @getimagesize($path);
            $this->assertNotFalse($info, $path . ' must be a valid image');
            $this->assertGreaterThanOrEqual(600, $info[0], $path . ' is too small');
        }

        $property = new \App\Models\Property();
        $this->assertStringContainsString('darli-placeholder.png', $property->placeholderUrl('ar'));
        $this->assertStringContainsString('darli-placeholder-en.png', $property->placeholderUrl('en'));
    }

    public function test_original_property_detail_is_served(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get('/properties/private-estate-magnificent-views')
            ->assertOk()
            ->assertSee('Private Estate Magnificent Views', false)
            ->assertSee('themes/darlii/css/style.css?v=1.3.3', false);
    }
}
