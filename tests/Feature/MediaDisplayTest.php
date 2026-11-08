<?php

namespace Tests\Feature;

use App\Models\AgentProfile;
use App\Models\Property;
use App\Models\PropertyMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_agent_avatar_url_uses_public_storage_and_falls_back_when_missing(): void
    {
        Storage::disk('public')->put('avatars/test-agent.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2n1cAAAAASUVORK5CYII='));

        $agent = AgentProfile::firstOrFail();
        $agent->avatar_path = 'avatars/test-agent.png';
        $agent->save();

        $this->assertSame(asset('storage/avatars/test-agent.png'), $agent->fresh()->avatar_url);

        Storage::disk('public')->delete('avatars/test-agent.png');
        $agent->refresh();
        $this->assertStringContainsString('avatar-placeholder.svg', $agent->avatar_url);
    }


    public function test_agent_index_renders_uploaded_avatar_image(): void
    {
        Storage::disk('public')->put('avatars/test-agent-index.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2n1cAAAAASUVORK5CYII='));

        $agent = AgentProfile::where('status', 'active')->latest('id')->firstOrFail();
        $agent->avatar_path = 'avatars/test-agent-index.png';
        $agent->save();

        $this->get('/agents')->assertOk()->assertSee('/storage/avatars/test-agent-index.png', false);

        Storage::disk('public')->delete('avatars/test-agent-index.png');
    }

    public function test_published_property_page_renders_uploaded_video(): void
    {
        $property = Property::where('status', 'published')->firstOrFail();
        $path = 'properties/' . $property->id . '/videos/test-page-video.mp4';
        Storage::disk('public')->put($path, 'video-bytes');

        $media = PropertyMedia::create([
            'property_id' => $property->id,
            'type' => 'video',
            'path' => $path,
            'disk' => 'public',
            'mime_type' => 'video/mp4',
            'size' => 11,
            'sort_order' => 99,
            'is_primary' => false,
        ]);

        $slug = $property->translation('ar')?->slug;
        $this->get('/properties/' . $slug)->assertOk()->assertSee('/storage/' . $path, false);

        Storage::disk('public')->delete($path);
        $media->delete();
    }

    public function test_property_video_source_uses_existing_public_file_and_hides_missing_file(): void
    {
        $property = Property::firstOrFail();
        Storage::disk('public')->put('properties/108/videos/test-video.mp4', 'video-bytes');

        $media = PropertyMedia::create([
            'property_id' => $property->id,
            'type' => 'video',
            'path' => 'properties/108/videos/test-video.mp4',
            'disk' => 'public',
            'mime_type' => 'video/mp4',
            'size' => 11,
            'sort_order' => 99,
            'is_primary' => false,
        ]);

        $property->load('media');
        $source = $property->videoSource();
        $this->assertSame('file', $source['kind']);
        $this->assertStringContainsString('/storage/properties/108/videos/test-video.mp4', $source['url']);

        Storage::disk('public')->delete('properties/108/videos/test-video.mp4');
        $property->load('media');
        $this->assertNull($property->videoSource());

        $media->delete();
    }
}
