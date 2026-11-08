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
            $this->get("/homepage-{$variant}")->assertRedirect('/')->assertStatus(301);
        }

        // روابط /en القديمة تعيد التوجيه إلى العنوان النظيف.
        $this->get('/en')->assertRedirect('/');

        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertOk()
            ->assertSee('Darlii', false)
            ->assertSee('Discover the Best Properties', false)
            ->assertDontSee('Homepage 1', false)
            ->assertDontSee('Homepage 5', false);
    }

    public function test_homepage_has_canonical_and_one_complete_hreflang_set(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="' . rtrim(config('app.url'), '/') . '">', $html);
        $this->assertSame(1, substr_count($html, '<link rel="alternate" hreflang="ar"'));
        $this->assertSame(1, substr_count($html, '<link rel="alternate" hreflang="x-default"'));
        $this->assertStringNotContainsString('darlii.botble.com', $html);
    }

    public function test_arabic_is_the_default_and_a_saved_locale_is_preserved(): void
    {
        $this->withHeaders(['Accept-Language' => 'fr-FR,fr;q=0.9'])
            ->get('/')
            ->assertOk()
            ->assertSee('dir="rtl"', false);

        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertOk()
            ->assertSee('dir="ltr"', false);
    }
}
