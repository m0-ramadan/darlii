<?php

namespace App\Services\Notifications;

use App\Models\FcmToken;
use App\Models\SiteNotification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
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

        // Send via FCM if enabled and credentials are available
        $sentCount = 0;
        $fcmStatus = 'saved_in_app';
        $isEnabled = (bool) setting('firebase.enabled', true);

        if ($isEnabled && Schema::hasTable('fcm_tokens')) {
            // Filter strictly for valid push tokens, excluding visitor tracking dummy IDs
            $tokensQuery = FcmToken::query()
                ->where('token', 'not like', 'device_%')
                ->where('device_type', '!=', 'web_visitor');

            if ($targetType === 'user' && $userId) {
                $tokensQuery->where('user_id', $userId);
            }

            $tokens = $tokensQuery->pluck('token')->filter()->unique()->values()->all();

            if (!empty($tokens)) {
                $credentials = $this->resolveFirebaseCredentials();

                if ($credentials['type'] === 'service_account') {
                    // Modern FCM HTTP v1 API
                    $sentCount = $this->dispatchToFcmV1(
                        $tokens,
                        $title,
                        $body,
                        $fullImageUrl,
                        $actionUrl,
                        $credentials['data'],
                        $credentials['project_id'],
                        $notification?->id
                    );
                    $fcmStatus = $sentCount > 0 ? 'sent' : 'failed';
                } elseif ($credentials['type'] === 'legacy_server_key') {
                    // FCM Legacy HTTP API
                    $sentCount = $this->dispatchToFcm(
                        $tokens,
                        $title,
                        $body,
                        $fullImageUrl,
                        $actionUrl,
                        $credentials['key'],
                        $notification?->id
                    );
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
     * التحقق من المفاتيح المتاحة (سواء Server Key قديم أو Service Account JSON حديث).
     */
    public function resolveFirebaseCredentials(): array
    {
        $serverKey = trim((string) setting('firebase.server_key', ''));
        $serviceAccountJson = trim((string) setting('firebase.service_account_json', ''));
        $projectId = trim((string) setting('firebase.project_id', 'khademate2'));

        // Check if server_key itself contains JSON (user pasted service account into server key input)
        if (str_starts_with($serverKey, '{') && str_ends_with($serverKey, '}')) {
            $parsed = json_decode($serverKey, true);
            if (!empty($parsed['client_email']) && !empty($parsed['private_key'])) {
                return [
                    'type' => 'service_account',
                    'data' => $parsed,
                    'project_id' => $parsed['project_id'] ?? $projectId,
                ];
            }
        }

        // Check service account json setting
        if (!empty($serviceAccountJson)) {
            $parsed = json_decode($serviceAccountJson, true);
            if (!empty($parsed['client_email']) && !empty($parsed['private_key'])) {
                return [
                    'type' => 'service_account',
                    'data' => $parsed,
                    'project_id' => $parsed['project_id'] ?? $projectId,
                ];
            }
        }

        // Check if a service account file exists on disk
        $filePath = storage_path('app/firebase-service-account.json');
        if (file_exists($filePath)) {
            $parsed = json_decode((string) file_get_contents($filePath), true);
            if (!empty($parsed['client_email']) && !empty($parsed['private_key'])) {
                return [
                    'type' => 'service_account',
                    'data' => $parsed,
                    'project_id' => $parsed['project_id'] ?? $projectId,
                ];
            }
        }

        // Check legacy server key
        if (!empty($serverKey)) {
            return [
                'type' => 'legacy_server_key',
                'key' => $serverKey,
            ];
        }

        return ['type' => 'none'];
    }

    /**
     * إرسال الدفعات إلى Firebase Cloud Messaging Legacy API.
     */
    protected function dispatchToFcm(
        array $tokens,
        string $title,
        string $body,
        ?string $imageUrl,
        ?string $actionUrl,
        string $serverKey,
        ?int $notificationId = null
    ): int {
        $sentCount = 0;
        $chunks = array_chunk($tokens, 500);
        $notifTag = 'darli-notif-' . ($notificationId ?: time());

        foreach ($chunks as $chunk) {
            try {
                $notificationPayload = [
                    'title' => $title,
                    'body' => $body,
                    'icon' => asset('/themes/homzen/images/logo/logo-darli.png'),
                    'sound' => 'default',
                    'click_action' => $actionUrl ?: url('/ar'),
                ];
                if ($imageUrl) {
                    $notificationPayload['image'] = $imageUrl;
                }

                $dataPayload = [
                    'title' => $title,
                    'body' => $body,
                    'icon' => asset('/themes/homzen/images/logo/logo-darli.png'),
                    'badge' => asset('/themes/homzen/images/logo/favicon.png'),
                    'url' => $actionUrl ?: url('/ar'),
                    'sound' => 'default',
                    'vibrate' => '300,100,300,100,300',
                    'tag' => $notifTag,
                    'id' => (string) ($notificationId ?: time()),
                ];
                if ($imageUrl) {
                    $dataPayload['image'] = $imageUrl;
                }

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ])->timeout(12)->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => array_values($chunk),
                    'notification' => $notificationPayload,
                    'data' => $dataPayload,
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $successInBatch = (int) ($json['success'] ?? 0);
                    $sentCount += $successInBatch;
                    Log::info("FCM Legacy Broadcast: Sent {$successInBatch}/" . count($chunk) . " successfully.");
                } else {
                    Log::warning('FCM Send Failed: ' . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error('FCM Dispatch Exception: ' . $e->getMessage());
            }
        }

        return $sentCount;
    }

    /**
     * إرسال الدفعات عبر Firebase Cloud Messaging HTTP v1 API.
     */
    protected function dispatchToFcmV1(
        array $tokens,
        string $title,
        string $body,
        ?string $imageUrl,
        ?string $actionUrl,
        array $serviceAccount,
        string $projectId,
        ?int $notificationId = null
    ): int {
        $accessToken = $this->getGoogleAccessToken($serviceAccount);
        if (!$accessToken) {
            Log::error('FCM v1: Could not obtain OAuth2 access token.');
            return 0;
        }

        $sentCount = 0;
        $endpoint = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        $notifTag = 'darli-notif-' . ($notificationId ?: time());

        foreach ($tokens as $token) {
            try {
                $message = [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => [
                        'title' => $title,
                        'body' => $body,
                        'url' => (string) ($actionUrl ?: url('/ar')),
                        'sound' => 'default',
                        'vibrate' => '300,100,300,100,300',
                        'tag' => $notifTag,
                        'id' => (string) ($notificationId ?: time()),
                    ],
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high',
                        ],
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'icon' => asset('/themes/homzen/images/logo/logo-darli.png'),
                            'badge' => asset('/themes/homzen/images/logo/favicon.png'),
                            'vibrate' => [300, 100, 300, 100, 300],
                            'requireInteraction' => true,
                            'renotify' => true,
                            'tag' => $notifTag,
                            'data' => [
                                'url' => (string) ($actionUrl ?: url('/ar')),
                            ],
                        ],
                        'fcm_options' => [
                            'link' => (string) ($actionUrl ?: url('/ar')),
                        ],
                    ],
                ];

                if ($imageUrl) {
                    $message['notification']['image'] = $imageUrl;
                    $message['webpush']['notification']['image'] = $imageUrl;
                    $message['data']['image'] = $imageUrl;
                }

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->timeout(8)->post($endpoint, [
                    'message' => $message,
                ]);

                if ($response->successful()) {
                    $sentCount++;
                } else {
                    $body = $response->body();
                    Log::warning('FCM v1 Single Send Failed: ' . $body);
                    if (str_contains($body, 'UNREGISTERED') || str_contains($body, 'NotRegistered')) {
                        FcmToken::where('token', $token)->delete();
                        Log::info("FCM v1: Pruned expired token: {$token}");
                    }
                }
            } catch (\Throwable $e) {
                Log::error('FCM v1 Single Send Exception: ' . $e->getMessage());
            }
        }

        Log::info("FCM v1 Broadcast finished. Sent {$sentCount}/" . count($tokens) . ' successfully.');
        return $sentCount;
    }

    /**
     * توليد Google OAuth2 Access Token باستخدام Service Account JSON عبر JWT.
     */
    protected function getGoogleAccessToken(array $serviceAccount): ?string
    {
        $cacheKey = 'fcm_v1_google_token_' . md5($serviceAccount['client_email'] ?? '');
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $clientEmail = $serviceAccount['client_email'] ?? null;
        $privateKey = $serviceAccount['private_key'] ?? null;
        if (!$clientEmail || !$privateKey) {
            return null;
        }

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        $success = openssl_sign(
            $base64UrlHeader . '.' . $base64UrlPayload,
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA256
        );

        if (!$success) {
            Log::error('FCM v1: openssl_sign failed for JWT assertion');
            return null;
        }

        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

        try {
            $response = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                $token = $response->json('access_token');
                $expiresIn = (int) ($response->json('expires_in') ?? 3600);
                Cache::put($cacheKey, $token, now()->addSeconds(max(60, $expiresIn - 300)));
                return $token;
            }

            Log::error('FCM v1 Token Exchange Failed: ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('FCM v1 Token Request Exception: ' . $e->getMessage());
        }

        return null;
    }
}
