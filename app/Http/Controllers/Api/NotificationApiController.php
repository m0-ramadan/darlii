<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\SiteNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /**
     * حفظ أو تحديث رمز جهاز المشترك (FCM Token).
     */
    public function saveToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'old_token' => ['nullable', 'string', 'max:500'],
            'device_type' => ['nullable', 'string', 'max:50'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $userId = $validated['user_id'] ?? auth()->id();

        if (!empty($validated['old_token']) && $validated['old_token'] !== $validated['token']) {
            $existing = FcmToken::where('token', $validated['old_token'])->first();
            $target = FcmToken::where('token', $validated['token'])->first();
            if ($existing && $target && $existing->id !== $target->id) {
                $existing->delete();
            } elseif ($existing) {
                $existing->update([
                    'token' => $validated['token'],
                    'user_id' => $userId ?: $existing->user_id,
                    'device_type' => $validated['device_type'] ?? 'web',
                    'user_agent' => substr((string) $request->userAgent(), 0, 500),
                    'last_used_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث رمز الجهاز بنجاح',
                    'id' => $existing->id,
                ]);
            }
        }

        $tokenRecord = FcmToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $userId,
                'device_type' => $validated['device_type'] ?? 'web',
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ رمز الجهاز بنجاح',
            'id' => $tokenRecord->id,
        ]);
    }

    /**
     * جلب آخر الإشعارات العامة والخاصة بالمستخدم مع عدد غير المقروءة.
     */
    public function list(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $query = SiteNotification::query()->forUser($userId);

        $unreadCount = (clone $query)->unread()->count();
        $notifications = $query->latest()->take(15)->get()->map(function (SiteNotification $item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'body' => $item->body,
                'image_url' => $item->image_url,
                'action_url' => $item->action_url,
                'target_type' => $item->target_type,
                'is_read' => $item->read_at !== null,
                'created_at_human' => $item->created_at?->diffForHumans() ?? '',
                'created_at' => $item->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * تحديد إشعار كمقروء.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $userId = auth()->id();

        $notification = SiteNotification::query()->forUser($userId)->find($id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود',
            ], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد الإشعار كمقروء',
        ]);
    }

    /**
     * تحديد كافة الإشعارات كمقروءة.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $userId = auth()->id();

        SiteNotification::query()->forUser($userId)->unread()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد كافة الإشعارات كمقروءة',
        ]);
    }
}
