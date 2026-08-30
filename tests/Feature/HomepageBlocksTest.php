<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageBlocksTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_homepage_four_blocks_render_dynamic_homzen_markup(): void
    {
        $this->seed();

        $blocks = [
            ['properties', ['style' => '5', 'title' => 'Featured', 'subtitle' => 'Properties', 'is_featured' => '1', 'limit' => '6'], 'property-item homeya-box'],
            ['services', ['style' => '4', 'title' => 'Services', 'services_quantity' => '1', 'services_title_1' => 'Buy a home'], 'flat-service-v4'],
            ['location', ['title' => 'Locations'], 'box-location'],
            ['agents', ['title' => 'Agents', 'account_ids' => '1,2,3'], 'box-agent style-card'],
            ['properties', ['style' => '6', 'title' => 'Recommended', 'is_featured' => '1', 'limit' => '4'], 'flat-property-v3'],
            ['testimonials', ['title' => 'Testimonials', 'testimonial_ids' => '1,2,3,4'], 'box-tes-item style-2'],
            ['services', ['style' => '5', 'title' => 'Benefits', 'services_quantity' => '1', 'services_title_1' => 'Consulting'], 'flat-benefit-v2'],
            ['blog-posts', ['title' => 'News', 'limit' => '3'], 'flat-blog-item hover-img'],
            ['call-to-action', ['title' => 'Join us', 'button_label' => 'Contact', 'button_url' => '/contact'], 'flat-banner'],
        ];

        foreach ($blocks as $index => [$name, $attributes, $expected]) {
            $response = $this->postJson('/en/ajax/render-ui-blocks', [
                'name' => $name,
                'shortcodeId' => 'test-' . $index,
                'attributes' => $attributes,
            ])->assertOk()->assertJsonPath('error', false);

            $html = $response->json('data');
            $this->assertStringContainsString($expected, $html);
            $this->assertStringNotContainsString('homzen.botble.com', $html);
        }
    }

    public function test_arabic_property_block_uses_arabic_slugs(): void
    {
        $this->seed();

        $html = $this->postJson('/ar/ajax/render-ui-blocks', [
            'name' => 'properties',
            'attributes' => ['style' => '5', 'is_featured' => '1', 'limit' => '2'],
        ])->assertOk()->json('data');

        $this->assertMatchesRegularExpression('~href="/ar/properties/[^"]+~u', $html);
        $this->assertMatchesRegularExpression('~[\x{0600}-\x{06FF}]~u', $html);
    }

    public function test_property_block_uses_default_attributes_when_they_are_omitted(): void
    {
        $this->seed();

        $this->getJson('/en/ajax/render-ui-blocks?name=properties')
            ->assertOk()
            ->assertJsonPath('error', false)
            ->assertJsonPath('data', fn (string $html): bool => str_contains($html, 'property-item homeya-box'));
    }
}
