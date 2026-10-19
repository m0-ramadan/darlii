<?php

namespace Tests\Feature;

use Database\Seeders\CoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferencePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_and_currency_are_saved_independently(): void
    {
        $this->seed(CoreSeeder::class);

        $this->withCookie('homzen_currency', 'EUR')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'ar')
            ->assertSessionHas('currency', 'EUR')
            ->assertCookie('homzen_locale', 'ar')
            ->assertCookie('homzen_currency', 'EUR');
    }

    public function test_language_switch_preserves_entity_and_query_string(): void
    {
        $this->seed();
        $property = \App\Models\Property::query()
            ->with('translations')
            ->whereHas('translations', fn ($query) => $query->where('locale', 'en')->where('slug', 'private-estate-magnificent-views'))
            ->firstOrFail();
        $arabicSlug = $property->translation('ar')->slug;

        $html = $this->withSession(['locale' => 'en'])
            ->get('/properties/private-estate-magnificent-views?type=sale&page=2')
            ->assertOk()
            ->getContent();

        // رابط تبديل اللغة يمرّ عبر /language/switch حتى يُحفظ الاختيار،
        // ثم يقوم LocaleController بترجمة الـslug وإعادة التوجيه.
        $expectedRedirect = '/properties/private-estate-magnificent-views?type=sale&page=2';
        $this->assertStringContainsString(
            '/language/switch/ar?redirect=' . rawurlencode($expectedRedirect),
            $html
        );
        $this->assertNotEmpty($arabicSlug);
    }

    public function test_footer_lists_all_languages_and_marks_the_current_one(): void
    {
        $html = $this->get('/about-us')->assertOk()->getContent();

        $this->assertStringContainsString('class="homzen-footer-languages darli-footer-languages"', $html);
        $this->assertStringContainsString('darli-footer-languages__link is-active', $html);
        $this->assertStringContainsString('aria-current="page"', $html);

        foreach (config('locales.names') as $code => $name) {
            $this->assertStringContainsString('/language/switch/' . $code . '?redirect=%2Fabout-us', $html);
            $this->assertStringContainsString($name, $html);
        }
    }

    public function test_explicit_language_switch_remembers_locale_and_keeps_the_translated_page(): void
    {
        $this->seed();
        $property = \App\Models\Property::query()
            ->with('translations')
            ->whereHas('translations', fn ($query) => $query->where('locale', 'en')->where('slug', 'private-estate-magnificent-views'))
            ->firstOrFail();
        $arabicSlug = $property->translation('ar')->slug;
        $redirect = rawurlencode('/properties/private-estate-magnificent-views?type=sale&page=2');

        $this->get('/language/switch/ar?redirect=' . $redirect)
            ->assertRedirect('/properties/' . $arabicSlug . '?type=sale&page=2')
            ->assertSessionHas('locale', 'ar')
            ->assertCookie('homzen_locale', 'ar');
    }

    public function test_clean_page_urls_render_using_the_saved_locale(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get('/about-us')
            ->assertOk();

        $this->withSession(['locale' => 'unsupported'])->get('/contact-us')
            ->assertOk();
    }
}
