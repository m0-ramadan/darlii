<?php

namespace App\Console\Commands;

use App\Models\FcmToken;
use App\Services\Notifications\FcmNotificationService;
use Illuminate\Console\Command;

class TestPushNotification extends Command
{
    protected $signature = 'notifications:test
        {token? : توكن الجهاز (FCM registration token)}
        {--title= : عنوان الإشعار}
        {--body= : نص الإشعار}
        {--url= : الرابط الذي يفتح عند الضغط}';

    protected $description = 'إرسال إشعار تجريبي إلى جهاز واحد للتأكد من عمل Push Notifications';

    public function handle(FcmNotificationService $service): int
    {
        $token = trim((string) $this->argument('token')) ?: FcmToken::query()
            ->where('token', 'not like', 'device_%')
            ->latest('id')
            ->value('token');

        if (! $token) {
            $this->error('لا يوجد توكن جهاز. مرّر التوكن كوسيط أو سجّل جهازًا من الموقع أولًا.');

            return self::FAILURE;
        }

        $title = (string) ($this->option('title') ?: 'دارلي — إشعار تجريبي');
        $body = (string) ($this->option('body') ?: 'تم إرسال هذا الإشعار من منصة دارلي للتأكد أن تنبيهات المتصفح تعمل.');
        $url = $this->option('url') ?: url('/');

        $this->info('إرسال إشعار إلى الجهاز: ' . substr($token, 0, 18) . '…');

        $result = $service->sendTest($token, $title, $body, (string) $url);

        if (! empty($result['ok'])) {
            $this->info('✅ تم قبول الإشعار من FCM بنجاح (HTTP ' . ($result['status'] ?? 200) . ').');
            $this->line('Message: ' . (is_array($result['body'] ?? null) ? json_encode($result['body'], JSON_UNESCAPED_UNICODE) : (string) ($result['body'] ?? '')));

            return self::SUCCESS;
        }

        $this->error('❌ فشل إرسال الإشعار.');
        $this->line('الخطأ: ' . ($result['error'] ?? 'unknown'));
        $this->line('Project: ' . ($result['project_id'] ?? '—') . ' | Key ID: ' . ($result['key_id'] ?? '—'));

        if (is_array($result['body'] ?? null)) {
            $this->line('Response: ' . json_encode($result['body'], JSON_UNESCAPED_UNICODE));
            $errorCode = $result['body']['error']['details'][0]['errorCode'] ?? null;
            if ($errorCode === 'UNREGISTERED') {
                $this->warn('التوكن غير مسجّل/منتهي على Firebase — افتح الموقع على الجهاز واسمح بالإشعارات من جديد.');
            }
        }

        if (($result['error'] ?? '') === 'oauth_failed') {
            $this->warn('مفاتيح حساب الخدمة غير صالحة. شغّل: php artisan firebase:check');
        }

        return self::FAILURE;
    }
}
