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
            ->get('/ar')
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

        $html = $this->get('/en/properties/private-estate-magnificent-views?type=sale&page=2')
            ->assertOk()
            ->getContent();

        $matched = preg_match(
            '~href="(/ar/properties/' . preg_quote($arabicSlug, '~') . '\?[^"#]+)~u',
            $html,
            $match
        );
        $this->assertSame(1, $matched, 'The Arabic language link must use the translated property slug.');
        parse_str(html_entity_decode((string) parse_url($match[1], PHP_URL_QUERY)), $query);
        $this->assertSame('sale', $query['type'] ?? null);
        $this->assertSame('2', $query['page'] ?? null);
    }

    public function test_footer_lists_all_languages_and_marks_the_current_one(): void
    {
        $html = $this->get('/ar/about-us')->assertOk()->getContent();

        $this->assertStringContainsString('class="homzen-footer-languages"', $html);
        $this->assertStringContainsString('class="homzen-footer-languages__link is-active"', $html);
        $this->assertStringContainsString('aria-current="page"', $html);

        foreach (config('locales.names') as $code => $name) {
            $this->assertStringContainsString('/language/switch/' . $code . '?redirect=%2Far%2Fabout-us', $html);
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
        $redirect = rawurlencode('/en/properties/private-estate-magnificent-views?type=sale&page=2');

        $this->get('/language/switch/ar?redirect=' . $redirect)
            ->assertRedirect('/ar/properties/' . $arabicSlug . '?type=sale&page=2')
            ->assertSessionHas('locale', 'ar')
            ->assertCookie('homzen_locale', 'ar');
    }

    public function test_clean_page_urls_render_using_the_saved_locale(): void
    {
        $this->withCookie('homzen_locale', 'en')
            ->get('/about-us')
            ->assertOk();

        $this->withCookie('homzen_locale', 'unsupported')->get('/contact-us')
            ->assertOk();
    }
}
