<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use App\Services\Admin\AuditLogger;
use App\Services\Settings\SettingsRepository;
use Illuminate\Http\Request;

/**
 * إدارة الأسئلة الشائعة وإعدادات صفحة الأسئلة الشائعة من لوحة التحكم.
 */
class FaqController extends Controller
{
    use InteractsWithAdminPanel;

    public const DEFAULT_CATEGORIES = [
        'الشراء والاستثمار العقاري',
        'الإيجار والوحدات السكنية',
        'الوكلاء والشركات المعتمدة',
        'التمويل والأقساط',
        'نشر العقارات',
    ];

    public function index(Request $request)
    {
        $this->authorizePermission('content.manage');
        $this->ensureDefaultSettings();

        $page = $this->getPage();
        $items = $this->getItems($page);

        $categories = $this->extractCategories($items);

        $selectedCategory = $request->query('category');
        if ($selectedCategory && $selectedCategory !== 'all') {
            $items = array_values(array_filter($items, fn($i) => ($i['category'] ?? '') === $selectedCategory));
        }

        $settings = Setting::query()->where('group', 'faq')->orderBy('sort_order')->get();

        return view('admin.faqs.index', [
            'items' => $items,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory ?: 'all',
            'settings' => $settings,
            'page' => $page,
            'title' => 'الأسئلة الشائعة',
            'subtitle' => 'إدارة الأسئلة والأجوبة وتصنيفاتها وإعدادات الترويسة وصندوق الدعم',
        ]);
    }

    public function create()
    {
        $this->authorizePermission('content.manage');

        $page = $this->getPage();
        $items = $this->getItems($page);
        $categories = $this->extractCategories($items);

        return view('admin.faqs.form', [
            'item' => null,
            'categories' => $categories,
            'title' => 'إضافة سؤال شائع جديد',
            'subtitle' => 'أدخل السؤال والإجابة وحدد التصنيف المناسب',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('content.manage');

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'question_en' => ['nullable', 'string', 'max:500'],
            'answer_en' => ['nullable', 'string'],
            'category_en' => ['nullable', 'string', 'max:100'],
        ]);

        $page = $this->getPage();
        $items = $this->getItems($page);

        $newId = empty($items) ? 1 : (max(array_column($items, 'id')) + 1);

        $newItem = [
            'id' => $newId,
            'category' => trim($validated['category']),
            'category_en' => trim($validated['category_en'] ?? '') ?: trim($validated['category']),
            'question' => trim($validated['question']),
            'question_en' => trim($validated['question_en'] ?? '') ?: trim($validated['question']),
            'answer' => trim($validated['answer']),
            'answer_en' => trim($validated['answer_en'] ?? '') ?: trim($validated['answer']),
        ];

        $items[] = $newItem;

        $this->saveItems($page, $items);

        AuditLogger::log('faq.created', $page, [], ['id' => $newId, 'question' => $newItem['question']]);

        return redirect()->route('admin.faqs.index')->with('success', 'تم إضافة السؤال الشائع بنجاح.');
    }

    public function edit($id)
    {
        $this->authorizePermission('content.manage');

        $page = $this->getPage();
        $items = $this->getItems($page);
        $item = collect($items)->firstWhere('id', (int) $id);

        abort_if(! $item, 404, 'السؤال غير موجود');

        $categories = $this->extractCategories($items);

        return view('admin.faqs.form', [
            'item' => $item,
            'categories' => $categories,
            'title' => 'تعديل السؤال الشائع',
            'subtitle' => 'تعديل تفاصيل السؤال والإجابة والتصنيف',
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorizePermission('content.manage');

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'question_en' => ['nullable', 'string', 'max:500'],
            'answer_en' => ['nullable', 'string'],
            'category_en' => ['nullable', 'string', 'max:100'],
        ]);

        $page = $this->getPage();
        $items = $this->getItems($page);

        $found = false;
        foreach ($items as &$item) {
            if ((int) ($item['id'] ?? 0) === (int) $id) {
                $item['category'] = trim($validated['category']);
                $item['category_en'] = trim($validated['category_en'] ?? '') ?: trim($validated['category']);
                $item['question'] = trim($validated['question']);
                $item['question_en'] = trim($validated['question_en'] ?? '') ?: trim($validated['question']);
                $item['answer'] = trim($validated['answer']);
                $item['answer_en'] = trim($validated['answer_en'] ?? '') ?: trim($validated['answer']);
                $found = true;
                break;
            }
        }
        unset($item);

        abort_if(! $found, 404, 'السؤال غير موجود');

        $this->saveItems($page, $items);

        AuditLogger::log('faq.updated', $page, [], ['id' => $id, 'question' => $validated['question']]);

        return redirect()->route('admin.faqs.index')->with('success', 'تم تعديل السؤال بنجاح.');
    }

    public function destroy($id)
    {
        $this->authorizePermission('content.manage');

        $page = $this->getPage();
        $items = $this->getItems($page);

        $filtered = array_values(array_filter($items, fn($i) => (int) ($i['id'] ?? 0) !== (int) $id));

        $this->saveItems($page, $filtered);

        AuditLogger::log('faq.deleted', $page, [], ['id' => $id]);

        return redirect()->route('admin.faqs.index')->with('success', 'تم حذف السؤال بنجاح.');
    }

    public function updateSettings(Request $request)
    {
        $this->authorizePermission('content.manage');

        $validated = $request->validate([
            'faq_hero_badge' => ['nullable', 'string', 'max:255'],
            'faq_hero_title' => ['required', 'string', 'max:255'],
            'faq_hero_subtitle' => ['nullable', 'string'],
            'faq_search_placeholder' => ['nullable', 'string', 'max:255'],
            'faq_cta_badge' => ['nullable', 'string', 'max:255'],
            'faq_cta_title' => ['required', 'string', 'max:255'],
            'faq_cta_desc' => ['nullable', 'string'],
            'faq_cta_btn_text' => ['required', 'string', 'max:100'],
            'faq_cta_btn_url' => ['required', 'string', 'max:255'],
            'faq_cta_whatsapp_text' => ['required', 'string', 'max:100'],
        ]);

        $settingsRepo = app(SettingsRepository::class);

        $map = [
            'faq.hero_badge' => $validated['faq_hero_badge'] ?? '',
            'faq.hero_title' => $validated['faq_hero_title'],
            'faq.hero_subtitle' => $validated['faq_hero_subtitle'] ?? '',
            'faq.search_placeholder' => $validated['faq_search_placeholder'] ?? '',
            'faq.cta_badge' => $validated['faq_cta_badge'] ?? '',
            'faq.cta_title' => $validated['faq_cta_title'],
            'faq.cta_desc' => $validated['faq_cta_desc'] ?? '',
            'faq.cta_btn_text' => $validated['faq_cta_btn_text'],
            'faq.cta_btn_url' => $validated['faq_cta_btn_url'],
            'faq.cta_whatsapp_text' => $validated['faq_cta_whatsapp_text'],
        ];

        $settingsRepo->setMany($map);

        // Keep page title in sync with hero title
        $page = $this->getPage();
        $tAr = $page->translations()->where('locale', 'ar')->first();
        if ($tAr && $tAr->title !== $validated['faq_hero_title']) {
            $tAr->update(['title' => $validated['faq_hero_title']]);
        }

        AuditLogger::log('faq.settings_updated', null, [], array_keys($map));

        return redirect()->route('admin.faqs.index', ['tab' => 'settings'])->with('success', 'تم حفظ إعدادات صفحة الأسئلة الشائعة بنجاح.');
    }

    private function getPage(): Page
    {
        return Page::query()->firstOrCreate(
            ['key' => 'faqs'],
            ['status' => 'published', 'published_at' => now()]
        );
    }

    private function getItems(Page $page): array
    {
        $t = $page->translations()->where('locale', 'ar')->first();
        $raw = $t?->content ?? '';

        if (! empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $hasMissingId = false;
                foreach ($decoded as $idx => &$item) {
                    if (! isset($item['id'])) {
                        $item['id'] = $idx + 1;
                        $hasMissingId = true;
                    }
                }
                unset($item);

                if ($hasMissingId) {
                    $this->saveItems($page, $decoded);
                }

                return $decoded;
            }
        }

        return [];
    }

    private function saveItems(Page $page, array $items): void
    {
        $json = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $tAr = $page->translations()->firstOrCreate(
            ['locale' => 'ar'],
            ['title' => 'الأسئلة الشائعة والأجوبة الوافية', 'slug' => 'faqs']
        );
        $tAr->update(['content' => $json]);

        $tEn = $page->translations()->firstOrCreate(
            ['locale' => 'en'],
            ['title' => 'Frequently Asked Questions (FAQ)', 'slug' => 'faqs']
        );
        $tEn->update(['content' => $json]);
    }

    private function extractCategories(array $items): array
    {
        $extracted = array_unique(array_filter(array_column($items, 'category')));

        return array_values(array_unique(array_merge(self::DEFAULT_CATEGORIES, $extracted)));
    }

    private function ensureDefaultSettings(): void
    {
        $definitions = [
            ['key' => 'faq.hero_badge', 'value' => 'مركز المساعدة ودليل المستخدمين', 'type' => 'text', 'label' => 'شارة الترويسة العليا', 'sort' => 1],
            ['key' => 'faq.hero_title', 'value' => 'الأسئلة الشائعة والأجوبة الوافية', 'type' => 'text', 'label' => 'العنوان الرئيسي لصفحة الأسئلة', 'sort' => 2],
            ['key' => 'faq.hero_subtitle', 'value' => 'إجابات شاملة على أهم الأسئلة حول شراء وبيع وتأجير العقارات، الأمور القانونية، والتعامل مع الوكلاء المعتمدين في بني سويف ومصر.', 'type' => 'textarea', 'label' => 'الوصف التوضيحي في الترويسة', 'sort' => 3],
            ['key' => 'faq.search_placeholder', 'value' => 'ابحث في الأسئلة الشائعة.. مثل: سند الملكية، الاستثمار في بني سويف، الإيجار ...', 'type' => 'text', 'label' => 'نص حقل البحث التوضيحي', 'sort' => 4],
            ['key' => 'faq.cta_badge', 'value' => 'ما زلت تبحث عن إجابة؟', 'type' => 'text', 'label' => 'الشارة في صندوق الدعم السفلي', 'sort' => 5],
            ['key' => 'faq.cta_title', 'value' => 'فريقنا مستعد لمساعدتك دائماً', 'type' => 'text', 'label' => 'عنوان صندوق الدعم السفلي', 'sort' => 6],
            ['key' => 'faq.cta_desc', 'value' => 'تواصل مع فريق الدعم لدينا للحصول على استشارة مجانية وإجابات دقيقة لجميع استفساراتك العقارية في بني سويف ومصر.', 'type' => 'textarea', 'label' => 'وصف صندوق الدعم السفلي', 'sort' => 7],
            ['key' => 'faq.cta_btn_text', 'value' => 'تواصل معنا الآن', 'type' => 'text', 'label' => 'نص زر التواصل', 'sort' => 8],
            ['key' => 'faq.cta_btn_url', 'value' => '/ar/contact-us', 'type' => 'text', 'label' => 'رابط زر التواصل', 'sort' => 9],
            ['key' => 'faq.cta_whatsapp_text', 'value' => 'محادثة واتساب سريعة', 'type' => 'text', 'label' => 'نص زر الواتساب', 'sort' => 10],
        ];

        foreach ($definitions as $def) {
            Setting::query()->firstOrCreate(
                ['key' => $def['key']],
                [
                    'group' => 'faq',
                    'value' => $def['value'],
                    'type' => $def['type'],
                    'label_ar' => $def['label'],
                    'is_public' => true,
                    'sort_order' => $def['sort'],
                ]
            );
        }
    }
}
