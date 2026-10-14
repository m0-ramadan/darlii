<?php

namespace App\Services\Notifications;

use App\Models\FcmToken;
use App\Models\SiteNotification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FcmNotificationService
{
    /**
     * إرسال إشعار (عام للجميع أو لمستخدم محدد) وحفظه في سجل الإشعارات وبثه عبر FCM.
     *
     * @param array $payload [
     *     'title' => string,
     *     'body' => string,
     *     'image_path' => string|null,
     *     'action_url' => string|null,
     *     'target_type' => 'all'|'user',
     *     'user_id' => int|null,
     * ]
     * @return array
     */
    public function send(array $payload): array
    {
        $targetType = $payload['target_type'] ?? 'all';
        $userId = ($targetType === 'user' && !empty($payload['user_id'])) ? (int) $payload['user_id'] : null;
        $title = trim($payload['title']);
        $body = trim($payload['body']);
        $imagePath = $payload['image_path'] ?? null;
        $actionUrl = $payload['action_url'] ?? url('/ar');

        // Resolve full image URL for FCM payload
        $fullImageUrl = null;
        if (!empty($imagePath)) {
            if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                $fullImageUrl = $imagePath;
            } elseif (str_starts_with($imagePath, 'storage/')) {
                $fullImageUrl = asset($imagePath);
            } else {
                $fullImageUrl = asset('storage/' . ltrim($imagePath, '/'));
            }
        }

        // Save in-app notification record
        $notification = null;
        if (Schema::hasTable('site_notifications')) {
            $notification = SiteNotification::create([
                'user_id' => $userId,
                'target_type' => $targetType,
                'title' => $title,
                'body' => $body,
                'image_path' => $imagePath,
                'action_url' => $actionUrl,
                'fcm_status' => 'pending',
                'sent_count' => 0,
            ]);
        }

        // Send via FCM if enabled and server key is available
        $sentCount = 0;
        $fcmStatus = 'saved_in_app';
        $isEnabled = (bool) setting('firebase.enabled', true);
        $serverKey = trim((string) setting('firebase.server_key', ''));

        if ($isEnabled && Schema::hasTable('fcm_tokens')) {
            // Gather recipient tokens
            $tokensQuery = FcmToken::query();
            if ($targetType === 'user' && $userId) {
                $tokensQuery->where('user_id', $userId);
            }
            $tokens = $tokensQuery->pluck('token')->filter()->unique()->values()->all();

            if (!empty($tokens)) {
                if (!empty($serverKey)) {
                    $sentCount = $this->dispatchToFcm($tokens, $title, $body, $fullImageUrl, $actionUrl, $serverKey);
                    $fcmStatus = $sentCount > 0 ? 'sent' : 'failed';
                } else {
                    $fcmStatus = 'ready_needs_server_key';
                }
            } else {
                $fcmStatus = 'no_registered_devices';
            }
        }

        if ($notification) {
            $notification->update([
                'sent_count' => $sentCount,
                'fcm_status' => $fcmStatus,
            ]);
        }

        return [
            'success' => true,
            'notification' => $notification,
            'sent_count' => $sentCount,
            'fcm_status' => $fcmStatus,
        ];
    }

    /**
     * إرسال الدفعات إلى Firebase Cloud Messaging Legacy API.
     */
    protected function dispatchToFcm(array $tokens, string $title, string $body, ?string $imageUrl, ?string $actionUrl, string $serverKey): int
    {
        $sentCount = 0;
        $chunks = array_chunk($tokens, 500);

        foreach ($chunks as $chunk) {
            try {
                $notificationPayload = [
                    'title' => $title,
                    'body' => $body,
                    'icon' => asset('/themes/homzen/images/logo/logo-darli.png'),
                ];
                if ($imageUrl) {
                    $notificationPayload['image'] = $imageUrl;
                }

                $dataPayload = [
                    'title' => $title,
                    'body' => $body,
                    'url' => $actionUrl ?: url('/ar'),
                ];
                if ($imageUrl) {
                    $dataPayload['image'] = $imageUrl;
                }

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => array_values($chunk),
                    'notification' => $notificationPayload,
                    'data' => $dataPayload,
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $successInBatch = (int) ($json['success'] ?? 0);
                    $sentCount += $successInBatch;
                } else {
                    Log::warning('FCM Send Failed: ' . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error('FCM Dispatch Exception: ' . $e->getMessage());
            }
        }

        return $sentCount;
    }
}

