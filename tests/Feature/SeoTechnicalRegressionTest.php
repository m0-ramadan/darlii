<?php

namespace Tests\Feature;

use App\Models\{AgentProfile, Career, Page, Post, Project, Property};
use App\Services\Seo\{PublicMedia, SitemapGenerator};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoTechnicalRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_out_of_range_and_malformed_pagination_returns_real_404(): void
    {
        foreach (['properties', 'projects', 'agents', 'blog', 'careers'] as $section) {
            $this->withHeaders(['Accept-Encoding' => 'gzip'])->get('/' . $section . '?page=9999')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
        foreach (['0', '-1', 'abc', '1.5', '%5B%5D'] as $value) {
            $this->get('/properties?page=' . $value)->assertNotFound();
        }
        $this->get('/properties?page%5B%5D=1')->assertNotFound();
    }

    public function test_filters_are_noindex_but_regular_listing_is_indexable(): void
    {
        $this->get('/properties?q=seo-no-matches')->assertOk()->assertSee('content="noindex,follow"', false);
        $this->get('/properties?sort=price_asc')->assertOk()->assertSee('content="noindex,follow"', false);
        $this->get('/properties?utm_source=test')->assertOk()->assertSee('content="index,follow"', false);
    }

    public function test_unpublished_details_and_static_pages_are_not_public(): void
    {
        foreach ([[Post::class, 'blog'], [Career::class, 'careers'], [Project::class, 'projects']] as [$class, $section]) {
            $model = $class::with('translations')->firstOrFail();
            $slug = $model->translation('ar')->slug;
            $model->update(['status' => 'draft']);
            $this->get('/' . $section . '/' . $slug)->assertNotFound();
        }
        $agent = AgentProfile::firstOrFail();
        $agent->update(['status' => 'pending']);
        $this->get('/agents/' . $agent->slug)->assertNotFound();
        Page::updateOrCreate(['key' => 'about-us'], ['status' => 'draft']);
        $this->get('/about-us')->assertNotFound();
    }

    public function test_home_has_one_heading_working_search_action_and_external_flag(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('/<h1\b/i', $html));
        $this->assertStringContainsString('?q={search_term_string}', $html);
        $this->assertStringNotContainsString('flags_sa_a', $html);
        $this->assertStringContainsString('images/flags/sa.svg', $html);
        $this->assertStringNotContainsString('hreflang="en"', $html);
    }

    public function test_media_resolves_existing_storage_and_rejects_missing_local_urls(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('properties/seo-existing.jpg', 'image');
        $media = app(PublicMedia::class);
        $this->assertSame(asset('storage/properties/seo-existing.jpg'), $media->url('properties/seo-existing.jpg'));
        $this->assertNull($media->url('properties/missing.jpg'));
        $this->assertNull($media->url('https://darlii.com/storage/properties/missing.jpg'));
        $this->assertNull($media->url('../private-file'));
        $this->assertSame('https://example.com/photo.jpg', $media->url('https://example.com/photo.jpg'));
    }

    public function test_translation_changes_update_parent_and_sitemap_timestamp(): void
    {
        $post = Post::with('translations')->where('status', 'published')->firstOrFail();
        $previous = $post->updated_at;
        $this->travel(1)->days();
        $translation = $post->translation('ar');
        $translation->update(['seo_title' => 'عنوان محدث لاختبار تاريخ تعديل المحتوى']);
        $this->assertTrue($post->fresh()->updated_at->greaterThan($previous));
        $entry = collect(app(SitemapGenerator::class)->urls())->firstWhere('loc', url('/blog/' . $translation->slug));
        $this->assertSame($post->fresh()->updated_at->toAtomString(), $entry['lastmod']);
        $this->travelBack();
    }

    public function test_expired_career_does_not_advertise_job_schema_or_application_form(): void
    {
        $career = Career::with('translations')->where('status', 'published')->firstOrFail();
        $career->update(['deadline' => now()->subDay()]);
        $this->get('/careers/' . $career->translation('ar')->slug)->assertOk()
            ->assertDontSee('"@type":"JobPosting"', false)
            ->assertDontSee('id="career-lead-message"', false)
            ->assertSee('انتهى موعد التقديم');
    }

    public function test_open_job_schema_contains_the_full_description_and_standard_employment_type(): void
    {
        $career = Career::with('translations')->where('status', 'published')->firstOrFail();
        $career->update(['deadline' => now()->addMonth(), 'employment_type' => 'دوام كامل']);
        $description = str_repeat('تفاصيل الوظيفة ومهام العمل والمؤهلات المطلوبة. ', 12);
        $career->translation('ar')->update(['description' => $description]);
        $html = $this->get('/careers/' . $career->translation('ar')->slug)->assertOk()->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $graph = json_decode(end($matches[1]), true, flags: JSON_THROW_ON_ERROR);
        $job = collect($graph)->firstWhere('@type', 'JobPosting');
        $this->assertSame('FULL_TIME', $job['employmentType']);
        $this->assertSame($description, $job['description']);
    }

    public function test_property_schema_has_breadcrumbs_and_does_not_publish_a_hidden_price(): void
    {
        $property = Property::with('translations')->whereIn('status', ['published', 'sold'])->firstOrFail();
        $property->price_type = 'contact';
        $property->save();
        $html = $this->get('/properties/' . $property->translation('ar')->slug)->assertOk()->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $graph = json_decode(end($matches[1]), true, flags: JSON_THROW_ON_ERROR);
        $listing = collect($graph)->firstWhere('@type', 'RealEstateListing');
        $this->assertArrayNotHasKey('price', $listing['offers']);
        $this->assertSame('Place', $listing['mainEntity']['@type']);
        $this->assertArrayHasKey('address', $listing['mainEntity']);
        $this->assertArrayNotHasKey('address', $listing);
        $this->assertArrayHasKey('businessFunction', $listing['offers']);
        $this->assertNotNull(collect($graph)->firstWhere('@type', 'BreadcrumbList'));
    }
}
