<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\Currency;
use App\Models\FcmToken;
use App\Models\Post;
use App\Models\Property;
use App\Models\SiteNotification;
use App\Models\User;
use Database\Seeders\AdminUsersSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CoreSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUsersSeeder::class);
        $this->seed(SettingsSeeder::class);
        app(\App\Services\Settings\SettingsRepository::class)->flush();

        $this->admin = User::where('email', 'admin@darlii.eg')->first();

        $this->client = User::create([
            'name' => 'عميل تجريبي',
            'email' => 'client@darlii.test',
            'password' => bcrypt('password'),
            'role' => 'client',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_notifications_index_and_stats(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/notifications');

        $response->assertStatus(200);
        $response->assertSee('نظام الإشعارات');
        $response->assertSee('سجل الإشعارات');
        $response->assertSee('المشتركون (رموز الأجهزة)');
        $response->assertSee('إرسال إشعار جديد');
    }

    public function test_admin_can_access_notifications_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/notifications/create');

        $response->assertStatus(200);
        $response->assertSee('إرسال إشعار جديد');
        $response->assertSee('عام للجميع (بث شامل)');
        $response->assertSee('شخص محدد');
        $response->assertSee('معاينة حية لشكل الإشعار');
        $response->assertSee($this->client->name);
    }

    public function test_admin_can_access_and_view_firebase_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/notifications/settings');

        $response->assertStatus(200);
        $response->assertSee('إعدادات إشعارات Firebase');
        $response->assertSee('AIzaSyBNv6O81fJnehjTBw12cc9IZOT-32Bfy9Q');
        $response->assertSee('khademate2.firebaseapp.com');
        $response->assertSee('504226887841');
        $response->assertSee('1:504226887841:web:cbc5abdc30304c6014d8e0');
    }

    public function test_admin_can_update_firebase_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/notifications/settings', [
            'firebase_enabled' => '1',
            'firebase_api_key' => 'AIzaSyBNv6O81fJnehjTBw12cc9IZOT-32Bfy9Q',
            'firebase_auth_domain' => 'khademate2.firebaseapp.com',
            'firebase_project_id' => 'khademate2',
            'firebase_storage_bucket' => 'khademate2.firebasestorage.app',
            'firebase_messaging_sender_id' => '504226887841',
            'firebase_app_id' => '1:504226887841:web:cbc5abdc30304c6014d8e0',
            'firebase_measurement_id' => 'G-01HJBBQZP7',
            'firebase_vapid_public_key' => 'BP_TEST_VAPID_KEY_12345',
            'firebase_server_key' => 'AAAA_TEST_SERVER_KEY_67890',
        ]);

        $response->assertRedirect('/admin/notifications/settings');
        $this->assertEquals('BP_TEST_VAPID_KEY_12345', setting('firebase.vapid_public_key'));
        $this->assertEquals('AAAA_TEST_SERVER_KEY_67890', setting('firebase.server_key'));
    }

    public function test_admin_can_send_broadcast_notification_with_uploaded_image(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->create('offer.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)->post('/admin/notifications', [
            'target_type' => 'all',
            'title' => 'عرض عقاري حصري في التجمع',
            'body' => 'خصم 10% لفترة محدودة على جميع شقق دارلي في القاهرة الجديدة.',
            'image_file' => $image,
            'action_url' => url('/ar/properties'),
        ]);

        $response->assertRedirect('/admin/notifications');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('site_notifications', [
            'title' => 'عرض عقاري حصري في التجمع',
            'target_type' => 'all',
            'user_id' => null,
        ]);

        $notification = SiteNotification::where('title', 'عرض عقاري حصري في التجمع')->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('storage/notifications/', $notification->image_path);
    }

    public function test_admin_can_send_notification_to_specific_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/notifications', [
            'target_type' => 'user',
            'user_id' => $this->client->id,
            'title' => 'تم قبول طلب حجز موعد المعاينة الخاص بك',
            'body' => 'مرحباً، تم تأكيد موعدك مع الوكيل العقاري غداً في تمام الساعة 5 مساءً.',
            'image_url' => 'https://example.com/check.png',
            'action_url' => url('/ar/appointments'),
        ]);

        $response->assertRedirect('/admin/notifications');

        $this->assertDatabaseHas('site_notifications', [
            'title' => 'تم قبول طلب حجز موعد المعاينة الخاص بك',
            'target_type' => 'user',
            'user_id' => $this->client->id,
            'image_path' => 'https://example.com/check.png',
        ]);
    }

    public function test_admin_can_delete_notification(): void
    {
        $notification = SiteNotification::create([
            'title' => 'إشعار للتجربة والحذف',
            'body' => 'تفاصيل الحذف',
            'target_type' => 'all',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/notifications/{$notification->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('site_notifications', ['id' => $notification->id]);
    }

    public function test_api_can_save_and_update_fcm_token(): void
    {
        $token = 'fcm_fake_token_' . uniqid();

        $response = $this->postJson('/api/notifications/fcm-token', [
            'token' => $token,
            'device_type' => 'web_chrome',
            'user_id' => $this->client->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('fcm_tokens', [
            'token' => $token,
            'user_id' => $this->client->id,
            'device_type' => 'web_chrome',
        ]);
    }

    public function test_api_can_list_notifications_and_mark_as_read(): void
    {
        $notif = SiteNotification::create([
            'title' => 'تنبيه وصول عرض جديد',
            'body' => 'تم إضافة عقار يطابق معاييرك.',
            'target_type' => 'all',
            'read_at' => null,
        ]);

        $listResponse = $this->getJson('/api/notifications/list');
        $listResponse->assertStatus(200);
        $listResponse->assertJson(['success' => true]);
        $this->assertGreaterThanOrEqual(1, $listResponse->json('unread_count'));

        $readResponse = $this->postJson("/api/notifications/{$notif->id}/read");
        $readResponse->assertStatus(200);
        $readResponse->assertJson(['success' => true]);

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_public_pages_render_notifications_widget_and_firebase_script(): void
    {
        $response = $this->get('/ar');

        $response->assertStatus(200);
        $response->assertSee('darli-notifications-wrapper');
        $response->assertSee('DarliFirebaseConfig');
        $response->assertSee('darli-notifications.js');
        $response->assertSee('AIzaSyBNv6O81fJnehjTBw12cc9IZOT-32Bfy9Q');
    }

    public function test_visitor_device_token_can_be_upgraded_to_push_token(): void
    {
        $visitorToken = 'visitor_device_abc123';
        $pushToken = 'fcm_push_token_xyz987';

        // 1. Initial visitor interaction / automatic register
        $resp1 = $this->postJson('/api/notifications/fcm-token', [
            'token' => $visitorToken,
            'device_type' => 'web_visitor',
        ]);
        $resp1->assertStatus(200);
        $this->assertDatabaseHas('fcm_tokens', [
            'token' => $visitorToken,
            'device_type' => 'web_visitor',
        ]);

        // 2. Permission granted, exchange old visitor token for push token
        $resp2 = $this->postJson('/api/notifications/fcm-token', [
            'token' => $pushToken,
            'old_token' => $visitorToken,
            'device_type' => 'web_push',
        ]);
        $resp2->assertStatus(200);

        $this->assertDatabaseHas('fcm_tokens', [
            'token' => $pushToken,
            'device_type' => 'web_push',
        ]);
        $this->assertDatabaseMissing('fcm_tokens', [
            'token' => $visitorToken,
        ]);
    }

    public function test_creating_property_with_broadcast_notification_creates_broadcast_notification(): void
    {
        $currency = Currency::first();

        $response = $this->actingAs($this->admin)->post('/admin/properties', [
            'price' => 2500000,
            'currency_id' => $currency->id,
            'purpose' => 'sale',
            'status' => 'published',
            'translations' => [
                'ar' => [
                    'name' => 'فيلا فاخرة للبيع بالشيخ زايد',
                    'slug' => 'luxury-villa-sheikh-zayed',
                    'description' => 'فيلا بتشطيبات ممتازة وموقع استراتيجي بالشيخ زايد.',
                ],
                'en' => [
                    'name' => 'Luxury Villa For Sale In Sheikh Zayed',
                    'slug' => 'luxury-villa-sheikh-zayed-en',
                    'description' => 'Excellent villa with strategic location.',
                ],
            ],
            'send_broadcast_notification' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('site_notifications', [
            'target_type' => 'all',
        ]);

        $notification = SiteNotification::where('title', 'like', '%فيلا فاخرة للبيع بالشيخ زايد%')->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('فيلا فاخرة', $notification->title);
        $this->assertStringContainsString('/ar/properties/', $notification->action_url);
    }

    public function test_creating_career_with_broadcast_notification_creates_broadcast_notification(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/careers', [
            'title_ar' => 'مهندس مبيعات عقارية',
            'title_en' => 'Real Estate Sales Engineer',
            'department' => 'Sales',
            'location' => 'القاهرة الجديدة',
            'employment_type' => 'دوام كامل',
            'status' => 'published',
            'summary_ar' => 'مطلوب مهندس مبيعات عقارية خبرة سنتين على الأقل.',
            'description_ar' => 'تفاصيل الوظيفة والشروط المطلوبة للتقديم.',
            'send_broadcast_notification' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $notification = SiteNotification::where('title', 'like', '%مهندس مبيعات عقارية%')->first();
        $this->assertNotNull($notification);
        $this->assertEquals('all', $notification->target_type);
        $this->assertStringContainsString('/ar/careers/', $notification->action_url);
    }

    public function test_creating_post_with_broadcast_notification_creates_broadcast_notification(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/posts', [
            'status' => 'published',
            'translations' => [
                'ar' => [
                    'title' => 'أفضل مناطق الاستثمار العقاري لعام 2026',
                    'slug' => 'best-real-estate-investments-2026',
                    'excerpt' => 'دليل شامل لأفضل الفرص الاستثمارية العقارية وأعلى عوائد.',
                    'content' => 'محتوى المقال التفصيلي حول الاستثمار العقاري.',
                ],
                'en' => [
                    'title' => 'Best Real Estate Investment Areas 2026',
                    'slug' => 'best-real-estate-investments-2026-en',
                    'excerpt' => 'A comprehensive guide for the best real estate opportunities.',
                    'content' => 'Detailed article content.',
                ],
            ],
            'send_broadcast_notification' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $notification = SiteNotification::where('title', 'like', '%أفضل مناطق الاستثمار العقاري%')->first();
        $this->assertNotNull($notification);
        $this->assertEquals('all', $notification->target_type);
        $this->assertStringContainsString('/ar/blog/', $notification->action_url);
    }

    public function test_updating_property_with_broadcast_notification_creates_broadcast_notification(): void
    {
        $currency = Currency::first();
        $property = Property::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference_code' => 'PROP-NOTIF-TEST',
            'price' => 1500000,
            'currency_id' => $currency->id,
            'purpose' => 'sale',
            'status' => 'published',
            'owner_id' => $this->admin->id,
            'created_by' => $this->admin->id,
        ]);
        $property->translations()->create([
            'locale' => 'ar',
            'name' => 'شقة مميزة مطلة على النيل',
            'slug' => 'nile-view-apartment',
            'description' => 'شقة رائعة مطلة مباشرة على كورنيش النيل.',
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/properties/{$property->id}", [
            'price' => 1800000,
            'currency_id' => $currency->id,
            'purpose' => 'sale',
            'status' => 'published',
            'translations' => [
                'ar' => [
                    'name' => 'شقة مميزة مطلة على النيل - تخفيض خاص',
                    'slug' => 'nile-view-apartment',
                    'description' => 'شقة رائعة مطلة مباشرة على كورنيش النيل بعد التخفيض.',
                ],
            ],
            'send_broadcast_notification' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $notification = SiteNotification::where('title', 'like', '%تحديث عقار%')->first();
        $this->assertNotNull($notification);
        $this->assertEquals('all', $notification->target_type);
    }
}
