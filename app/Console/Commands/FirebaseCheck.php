<?php

namespace App\Console\Commands;

use App\Services\Notifications\FcmNotificationService;
use Illuminate\Console\Command;

class FirebaseCheck extends Command
{
    protected $signature = 'firebase:check';

    protected $description = 'فحص صلاحية مفاتيح Firebase/FCM وحالة حساب الخدمة';

    public function handle(FcmNotificationService $service): int
    {
        $result = $service->validateCredentials();

        $this->table(['العنصر', 'القيمة'], [
            ['نوع الاعتماد', $result['type'] ?? '—'],
            ['المشروع', $result['project_id'] ?: '—'],
            ['حساب الخدمة', $result['client_email'] ?: '—'],
            ['Key ID', $result['key_id'] ?: '—'],
            ['الحالة', $result['ok'] ? '✅ صالح (تم الحصول على Access Token)' : '❌ غير صالح'],
            ['الخطأ', $result['error'] ?: '—'],
        ]);

        if (! $result['ok']) {
            $this->newLine();
            $this->error('مفاتيح Firebase غير صالحة. أعد توليد Service Account Key من:');
            $this->line('Firebase Console → Project settings → Service accounts → Generate new private key');
            $this->line('ثم استبدل storage/app/firebase-service-account.json وشغّل: php artisan firebase:check');

            return self::FAILURE;
        }

        $this->info('مفاتيح Firebase تعمل بشكل صحيح.');

        return self::SUCCESS;
    }
}
