<?php

namespace Tests\Feature;

use App\Services\Performance\HomepagePerformance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepagePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_keeps_hero_and_forms_with_non_blocking_styles_and_inline_icons(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('id="darli-home-critical"', $html);
        $this->assertStringContainsString('media="print" onload="this.media=\'all\'"', $html);
        $this->assertStringContainsString('<noscript><link rel="stylesheet"', $html);
        $this->assertStringNotContainsString('plugins/lucide/lucide.min.js', $html);
        $this->assertStringContainsString('class="lucide lucide-search', $html);
        $this->assertStringContainsString('darli-hero-heading', $html);
        $this->assertStringContainsString('darli-mobile-search', $html);
        $this->assertStringContainsString('id="modalLogin"', $html);
        $this->assertStringNotContainsString('type="speculationrules"', $html);
    }

    public function test_visits_are_still_counted_once_after_each_response(): void
    {
        $views = fn () => (int) \App\Models\DailyStat::query()->where('metric', 'page_views.home')->whereDate('stat_date', today())->sum('value');
        $before = $views();
        $this->get('/')->assertOk();
        $this->assertSame($before + 1, $views());
        $this->get('/')->assertOk();
        $this->assertSame($before + 2, $views());
    }

    public function test_unknown_icons_keep_the_existing_client_fallback(): void
    {
        app()->setLocale('ar');
        $html = '<i data-lucide="unknown-future-icon"></i><script defer src="/themes/darlii/plugins/lucide/lucide.min.js"></script>';
        $optimized = app(HomepagePerformance::class)->optimize($html);
        $this->assertStringContainsString('unknown-future-icon', $optimized);
        $this->assertStringContainsString('lucide.min.js', $optimized);
    }

    public function test_english_styles_are_preserved_and_never_replaced_with_the_rtl_bundle(): void
    {
        app()->setLocale('en');
        $html = '<link rel="stylesheet" href="/bootstrap-ltr.css"><i data-lucide="search"></i>';
        $this->assertSame($html, app(HomepagePerformance::class)->optimize($html));
    }

    public function test_images_reserve_their_intrinsic_ratio_without_changing_existing_sizes(): void
    {
        app()->setLocale('ar');
        $path = 'themes/darlii/images/darli-hero-cairo-photo-clean-900.webp';
        [$width, $height] = getimagesize(public_path($path));
        $html = app(HomepagePerformance::class)->optimize('<img src="/' . $path . '"><img src="/' . $path . '" width="60" height="40">');
        $this->assertStringContainsString('width="' . $width . '" height="' . $height . '"', $html);
        $this->assertStringContainsString('width="60" height="40"', $html);
    }

    public function test_coverage_and_bundles_are_current_for_every_source_stylesheet(): void
    {
        $coverage = json_decode(file_get_contents(resource_path('performance/homepage-coverage.json')), true);
        $manifest = json_decode(file_get_contents(resource_path('performance/homepage.json')), true);
        foreach ($manifest['styles'] as $path) {
            $this->assertSame($coverage[$path]['sha256'], hash_file('sha256', public_path($path)), $path . ' requires regenerating performance assets.');
        }
        $this->assertFileExists(public_path($manifest['bundle']));
        $this->assertFileExists(public_path($manifest['critical']));
    }
}
