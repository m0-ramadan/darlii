<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\Settings\SettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_mode_blocks_the_public_site_but_not_admin_routes(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'general.maintenance_mode'],
            ['group' => 'maintenance', 'value' => '1', 'type' => 'boolean']
        );
        Setting::query()->updateOrCreate(
            ['key' => 'maintenance.title'],
            ['group' => 'maintenance', 'value' => 'صيانة مجدولة', 'type' => 'text']
        );
        app(SettingsRepository::class)->flush();

        $this->get('/ar')
            ->assertStatus(503)
            ->assertSee('صيانة مجدولة')
            ->assertHeader('Retry-After', '3600');

        $this->get('/admin/login')->assertOk();
    }
}
