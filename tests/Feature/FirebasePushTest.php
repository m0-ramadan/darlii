<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Notifications\FcmNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FirebasePushTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();
        $this->seed();
    }

    private function fakeGoogleOauth(bool $success = true): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => $success
                ? Http::response(['access_token' => 'test-access-token', 'expires_in' => 3600])
                : Http::response(['error' => 'invalid_grant', 'error_description' => 'Invalid JWT Signature.'], 400),
            'https://fcm.googleapis.com/v1/projects/*/messages:send' => Http::response([
                'name' => 'projects/khademate2/messages/123',
            ]),
        ]);
    }

    public function test_send_test_uses_fcm_http_v1_and_succeeds(): void
    {
        $this->fakeGoogleOauth(true);

        $result = app(FcmNotificationService::class)->sendTest(
            'test-device-token',
            'عنوان تجربة',
            'نص تجربة',
            'https://darlii.com/'
        );

        $this->assertTrue($result['ok']);
        $this->assertSame(200, $result['status']);
        $this->assertSame('khademate2', $result['project_id']);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'fcm.googleapis.com/v1/projects/khademate2/messages:send'));
        Http::assertSent(fn ($request) => str_contains($request->body(), 'notification-icon.png')
            && str_contains($request->body(), 'notification-badge.png'));
    }

    public function test_notification_icon_and_badge_assets_are_valid_squares(): void
    {
        foreach (['notification-icon.png' => 512, 'notification-badge.png' => 96] as $file => $size) {
            $path = public_path('themes/darlii/images/logo/' . $file);
            $this->assertFileExists($path);
            $info = getimagesize($path);
            $this->assertNotFalse($info);
            $this->assertSame($size, $info[0]);
            $this->assertSame($size, $info[1]);
        }
    }

    public function test_service_worker_uses_the_new_notification_icons(): void
    {
        $sw = file_get_contents(public_path('firebase-messaging-sw.js'));

        $this->assertStringContainsString('notification-icon.png', $sw);
        $this->assertStringContainsString('notification-badge.png', $sw);
        $this->assertStringNotContainsString('logo-darli.png', $sw);
    }

    public function test_send_test_reports_invalid_service_account_credentials(): void
    {
        $this->fakeGoogleOauth(false);

        $result = app(FcmNotificationService::class)->sendTest('test-device-token', 't', 'b');

        $this->assertFalse($result['ok']);
        $this->assertSame('oauth_failed', $result['error']);
        $this->assertNotEmpty($result['key_id']);
    }

    public function test_admin_can_send_test_push_to_a_single_token(): void
    {
        $this->fakeGoogleOauth(true);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/notifications/test-push', ['token' => 'test-device-token'])
            ->assertRedirect(route('admin.notifications.settings'))
            ->assertSessionHas('success');
    }

    public function test_admin_test_push_shows_clear_error_when_credentials_are_invalid(): void
    {
        $this->fakeGoogleOauth(false);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/notifications/test-push', ['token' => 'test-device-token'])
            ->assertRedirect(route('admin.notifications.settings'))
            ->assertSessionHas('error');
    }
}
