<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_four_is_the_only_public_homepage(): void
    {
        foreach ([1, 2, 3, 4, 5] as $variant) {
            $this->get("/homepage-{$variant}")->assertRedirect('/ar')->assertStatus(301);
            $this->get("/ar/homepage-{$variant}")->assertRedirect('/ar')->assertStatus(301);
        }

        $this->get('/en')
            ->assertOk()
            ->assertSee('<title>Homepage 4</title>', false)
            ->assertSee('id="page-4"', false)
            ->assertDontSee('Homepage 1', false)
            ->assertDontSee('Homepage 5', false);
    }

    public function test_homepage_has_canonical_and_one_complete_hreflang_set(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="' . config('app.url') . '/en">', $html);
        foreach (['en', 'ar', 'fr', 'id', 'vi', 'x-default'] as $locale) {
            $this->assertSame(1, substr_count($html, '<link rel="alternate" hreflang="' . $locale . '"'));
        }
        $this->assertStringNotContainsString('homzen.botble.com', $html);
    }

    public function test_arabic_is_the_default_and_a_saved_locale_is_preserved(): void
    {
        $this->withHeaders(['Accept-Language' => 'fr-FR,fr;q=0.9'])->get('/')->assertRedirect('/ar');
        $this->withCookie('homzen_locale', 'fr')->get('/')->assertRedirect('/fr');
    }
}
