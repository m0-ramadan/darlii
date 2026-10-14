<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\Setting;
use App\Models\SiteNotification;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use App\Services\Notifications\FcmNotificationService;
use App\Services\Settings\SettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NotificationController extends Controller
{
    use InteractsWithAdminPanel;

    public function __construct(
        protected FcmNotificationService $fcmService,
        protected SettingsRepository $settingsRepo
    ) {}

    /**
     * عرض سجل الإشعارات المرسلة والإحصائيات.
     */
    public function index(Request $request): View
    {
        $this->authorizePermission('settings.manage');
        $this->ensureDefaultSettings();

        $query = SiteNotification::query()->with('user');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($target = $request->input('target_type')) {
            if (in_array($target, ['all', 'user'], true)) {
                $query->where('target_type', $target);
            }
        }

        $notifications = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total_sent' => SiteNotification::count(),
            'total_devices' => FcmToken::count(),
            'is_firebase_enabled' => (bool) setting('firebase.enabled', true),
            'has_server_key' => !empty(setting('firebase.server_key')),
        ];

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'stats' => $stats,
            'search' => $request->input('search', ''),
            'selectedTarget' => $request->input('target_type', ''),
            'title' => 'نظام الإشعارات',
            'subtitle' => 'إرسال ومتابعة الإشعارات الفورية للزوار والمستخدمين والتحكم بإعدادات Firebase',
        ]);
    }

    /**
     * نموذج إرسال إشعار جديد (عام أو مخصص لمستخدم).
     */
    public function create(): View
    {
        $this->authorizePermission('settings.manage');

        // Fetch users list for targeting
        $users = User::query()
            ->select(['id', 'name', 'email', 'job_title'])
            ->orderBy('name')
            ->limit(300)
            ->get();

        return view('admin.notifications.create', [
            'users' => $users,
            'title' => 'إرسال إشعار جديد',
            'subtitle' => 'بث إشعار عام لجميع المشتركين أو إرسال إشعار خاص لشخص محدد مع إمكانية إرفاق صورة',
        ]);
    }

    /**
     * تنفيذ إرسال الإشعار وحفظه في السجل وبثه عبر Firebase.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission('settings.manage');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'target_type' => ['required', 'in:all,user'],
            'user_id' => ['nullable', 'required_if:target_type,user', 'exists:users,id'],
            'image_file' => ['nullable', 'image', 'max:5120'], // max 5MB
            'image_url' => ['nullable', 'url', 'max:500'],
            'action_url' => ['nullable', 'string', 'max:500'],
        ], [
            'title.required' => 'يرجى إدخال عنوان الإشعار.',
            'body.required' => 'يرجى إدخال نص الإشعار.',
            'user_id.required_if' => 'يرجى تحديد المستخدم المستهدف عند اختيار إشعار لشخص محدد.',
            'image_file.image' => 'الملف المرفوع يجب أن يكون صورة صالحة.',
            'image_file.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('notifications', 'public');
            $imagePath = 'storage/' . $path;
        } elseif (!empty($validated['image_url'])) {
            $imagePath = $validated['image_url'];
        }

        $result = $this->fcmService->send([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'image_path' => $imagePath,
            'action_url' => $validated['action_url'] ?: url('/ar'),
            'target_type' => $validated['target_type'],
            'user_id' => $validated['user_id'] ?? null,
        ]);

        AuditLogger::log('notifications.send', $result['notification'] ?? null, [], [], 'إرسال إشعار: ' . $validated['title']);

        $targetText = $validated['target_type'] === 'all' ? 'عام لجميع المشتركين' : 'للمستخدم المحدد';
        $sentCount = $result['sent_count'] ?? 0;

        $msg = "تم حفظ الإشعار بنجاح وإرساله ($targetText). ";
        if ($sentCount > 0) {
            $msg .= "تم البث الفوري إلى {$sentCount} جهاز مسجل.";
        } else {
            $msg .= "سيظهر في صندوق إشعارات الموقع فور تسجيل الدخول أو زيارة المنصة.";
        }

        return redirect()->route('admin.notifications.index')->with('success', $msg);
    }

    /**
     * صفحة إعدادات Firebase ونظام الإشعارات.
     */
    public function settings(): View
    {
        $this->authorizePermission('settings.manage');
        $this->ensureDefaultSettings();

        $settings = Setting::query()
            ->where('group', 'firebase')
            ->orderBy('sort_order')
            ->get();

        return view('admin.notifications.settings', [
            'settings' => $settings,
            'title' => 'إعدادات إشعارات Firebase',
            'subtitle' => 'ربط المنصة بحساب Firebase Web App وإعداد مفاتيح Web Push و Server Key',
        ]);
    }

    /**
     * حفظ وتحديث إعدادات Firebase ونظام الإشعارات.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $this->authorizePermission('settings.manage');

        $this->ensureDefaultSettings();

        $allowedKeys = [
            'firebase.enabled',
            'firebase.api_key',
            'firebase.auth_domain',
            'firebase.project_id',
            'firebase.storage_bucket',
            'firebase.messaging_sender_id',
            'firebase.app_id',
            'firebase.measurement_id',
            'firebase.vapid_public_key',
            'firebase.server_key',
        ];

        foreach ($allowedKeys as $key) {
            if ($key === 'firebase.enabled') {
                $val = $request->boolean('firebase_enabled') ? '1' : '0';
            } else {
                $inputName = str_replace('.', '_', $key);
                $val = (string) $request->input($inputName, '');
            }

            Setting::query()->where('key', $key)->update(['value' => $val]);
        }

        $this->settingsRepo->flush();

        AuditLogger::log('settings.update', null, [], [], 'تحديث إعدادات Firebase والإشعارات');

        return redirect()->route('admin.notifications.settings')
            ->with('success', 'تم حفظ وتحديث إعدادات Firebase والإشعارات بنجاح.');
    }

    /**
     * حذف إشعار من السجل.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->authorizePermission('settings.manage');

        $notification = SiteNotification::findOrFail($id);

        if ($notification->image_path && str_starts_with($notification->image_path, 'storage/')) {
            $rel = str_replace('storage/', '', $notification->image_path);
            Storage::disk('public')->delete($rel);
        }

        $notification->delete();

        AuditLogger::log('notifications.delete', $notification, [], [], 'حذف إشعار من السجل رقم: ' . $id);

        return redirect()->back()->with('success', 'تم حذف الإشعار من السجل بنجاح.');
    }

    /**
     * التأكد من وجود إعدادات Firebase الافتراضية في قاعدة البيانات.
     */
    private function ensureDefaultSettings(): void
    {
        $definitions = [
            ['key' => 'firebase.enabled', 'value' => '1', 'type' => 'boolean', 'label' => 'تفعيل نظام إشعارات Firebase و Web Push', 'help' => 'تفعيل إرسال إشعارات الدفع الفورية إلى المتصفحات والهواتف', 'sort' => 1],
            ['key' => 'firebase.api_key', 'value' => 'AIzaSyBNv6O81fJnehjTBw12cc9IZOT-32Bfy9Q', 'type' => 'text', 'label' => 'Firebase Web API Key', 'help' => 'مفتاح الويب من إعدادات مشروع Firebase', 'sort' => 2],
            ['key' => 'firebase.auth_domain', 'value' => 'khademate2.firebaseapp.com', 'type' => 'text', 'label' => 'Firebase Auth Domain', 'help' => 'نطاق المصادقة من إعدادات مشروع Firebase', 'sort' => 3],
            ['key' => 'firebase.project_id', 'value' => 'khademate2', 'type' => 'text', 'label' => 'Firebase Project ID', 'help' => 'معرف المشروع في Firebase', 'sort' => 4],
            ['key' => 'firebase.storage_bucket', 'value' => 'khademate2.firebasestorage.app', 'type' => 'text', 'label' => 'Firebase Storage Bucket', 'help' => 'مستودع التخزين في Firebase', 'sort' => 5],
            ['key' => 'firebase.messaging_sender_id', 'value' => '504226887841', 'type' => 'text', 'label' => 'Firebase Messaging Sender ID', 'help' => 'معرف مرسل الرسائل', 'sort' => 6],
            ['key' => 'firebase.app_id', 'value' => '1:504226887841:web:cbc5abdc30304c6014d8e0', 'type' => 'text', 'label' => 'Firebase Web App ID', 'help' => 'معرف تطبيق الويب في Firebase', 'sort' => 7],
            ['key' => 'firebase.measurement_id', 'value' => 'G-01HJBBQZP7', 'type' => 'text', 'label' => 'Measurement ID (Analytics)', 'help' => 'معرف القياس والتحليلات', 'sort' => 8],
            ['key' => 'firebase.vapid_public_key', 'value' => '', 'type' => 'text', 'label' => 'Firebase Web Push VAPID Public Key', 'help' => 'مفتاح Web Push العام (Key pair من تبويب Cloud Messaging في لوحة تحكم Firebase)', 'sort' => 9],
            ['key' => 'firebase.server_key', 'value' => '', 'type' => 'text', 'label' => 'Firebase Cloud Messaging Server Key (مفتاح السيرفر)', 'help' => 'مفتاح خادم FCM أو التوكن المعتمد لإرسال الإشعارات من السيرفر مباشرة', 'sort' => 10],
        ];

        foreach ($definitions as $def) {
            Setting::query()->firstOrCreate(
                ['key' => $def['key']],
                [
                    'group' => 'firebase',
                    'value' => $def['value'],
                    'type' => $def['type'],
                    'label_ar' => $def['label'],
                    'help_ar' => $def['help'] ?? null,
                    'is_public' => true,
                    'sort_order' => $def['sort'],
                ]
            );
        }
    }
}
