@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@php
    $activeTab = request()->query('tab', 'items');
@endphp

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="/ar/faqs" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'external', 'size' => 14])
            عرض الصفحة بالموقع
        </a>
        <a href="{{ admin_route('admin.faqs.create') }}" class="ap-btn ap-btn--primary ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16])
            إضافة سؤال جديد
        </a>
    </div>
@endsection

@section('content')
    {{-- Tabs --}}
    <div class="ap-tabs mb-4">
        <a href="{{ admin_route('admin.faqs.index', ['tab' => 'items']) }}"
            class="ap-tab {{ $activeTab === 'items' ? 'is-active' : '' }}">
            @include('admin.partials.icon', ['iconName' => 'help-circle', 'size' => 16])
            قائمة الأسئلة ({{ count($items) }})
        </a>
        <a href="{{ admin_route('admin.faqs.index', ['tab' => 'settings']) }}"
            class="ap-tab {{ $activeTab === 'settings' ? 'is-active' : '' }}">
            @include('admin.partials.icon', ['iconName' => 'settings', 'size' => 16])
            إعدادات نصوص الصفحة والترويسة
        </a>
    </div>

    @if ($activeTab === 'items')
        {{-- Category Filters --}}
        <div class="ap-card mb-4">
            <div class="ap-card__body py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted fw-bold" style="font-size: 13px;">تصفية حسب القسم:</span>
                    <a href="{{ admin_route('admin.faqs.index', ['tab' => 'items', 'category' => 'all']) }}"
                        class="ap-btn {{ $selectedCategory === 'all' ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm"
                        style="border-radius: 9999px;">
                        الكل ({{ count($items) }})
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ admin_route('admin.faqs.index', ['tab' => 'items', 'category' => $cat]) }}"
                            class="ap-btn {{ $selectedCategory === $cat ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm"
                            style="border-radius: 9999px;">
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- FAQs List Card --}}
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">
                    @include('admin.partials.icon', ['iconName' => 'help-circle', 'size' => 18])
                    الأسئلة المسجلة
                </h2>
                <span class="ap-badge">{{ count($items) }} سؤال</span>
            </div>

            <div class="ap-card__body p-0">
                @if (empty($items))
                    <div class="ap-empty py-5 text-center">
                        @include('admin.partials.icon', ['iconName' => 'help-circle', 'size' => 48])
                        <p class="mt-2 text-muted">لا توجد أسئلة مسجلة في هذا القسم.</p>
                        <a href="{{ admin_route('admin.faqs.create') }}" class="ap-btn ap-btn--primary ap-btn--sm mt-3">
                            إضافة سؤال جديد
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="ap-table w-100">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="width: 180px;">القسم</th>
                                    <th>السؤال</th>
                                    <th>مقتطف الإجابة</th>
                                    <th style="width: 130px; text-align: left;">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $idx => $item)
                                    <tr>
                                        <td>
                                            <span class="text-muted fw-bold">{{ $idx + 1 }}</span>
                                        </td>
                                        <td>
                                            <span class="ap-badge"
                                                style="background: rgba(245, 158, 11, 0.15); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.3);">
                                                {{ $item['category'] ?? 'عام' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold"
                                                style="color: var(--ap-text);">{{ $item['question'] ?? '' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted"
                                                style="font-size: 13px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $item['answer'] ?? '' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php $itemId = $item['id'] ?? ($idx + 1); @endphp
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="{{ admin_route('admin.faqs.edit', $itemId) }}"
                                                    class="ap-btn ap-btn--ghost ap-btn--xs" title="تعديل">
                                                    @include('admin.partials.icon', [
                                                        'iconName' => 'edit',
                                                        'size' => 14,
                                                    ])
                                                </a>
                                                <form method="post"
                                                    action="{{ admin_route('admin.faqs.destroy', $itemId) }}"
                                                    onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟');"
                                                    style="display: inline-block;">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit"
                                                        class="ap-btn ap-btn--ghost ap-btn--xs text-danger" title="حذف">
                                                        @include('admin.partials.icon', [
                                                            'iconName' => 'trash',
                                                            'size' => 14,
                                                        ])
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Settings Tab --}}
        <form method="post" action="{{ admin_route('admin.faqs.settings.update') }}">
            @csrf
            @method('put')

            <div class="ap-grid ap-grid--2" style="align-items: start;">
                {{-- Hero Settings --}}
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'layout', 'size' => 18])
                            إعدادات ترويسة الصفحة (Hero Section)
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">شارة الترويسة العليا (Badge)</label>
                            <input type="text" name="faq_hero_badge" class="ap-input"
                                value="{{ old('faq_hero_badge', setting('faq.hero_badge', 'مركز المساعدة ودليل المستخدمين')) }}">
                            <p class="ap-help">النص الصغير الذي يظهر أعلى العنوان مع أيقونة المساعدة.</p>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">العنوان الرئيسي <span>*</span></label>
                            <input type="text" name="faq_hero_title" class="ap-input" required
                                value="{{ old('faq_hero_title', setting('faq.hero_title', 'الأسئلة الشائعة والأجوبة الوافية')) }}">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">الوصف التوضيحي</label>
                            <textarea name="faq_hero_subtitle" class="ap-textarea" rows="3">{{ old('faq_hero_subtitle', setting('faq.hero_subtitle', 'إجابات شاملة على أهم الأسئلة حول شراء وبيع وتأجير العقارات، الأمور القانونية، والتعامل مع الوكلاء المعتمدين في بني سويف ومصر.')) }}</textarea>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">نص حقل البحث التوضيحي (Placeholder)</label>
                            <input type="text" name="faq_search_placeholder" class="ap-input"
                                value="{{ old('faq_search_placeholder', setting('faq.search_placeholder', 'ابحث في الأسئلة الشائعة.. مثل: سند الملكية، الاستثمار في بني سويف، الإيجار ...')) }}">
                        </div>
                    </div>
                </div>

                {{-- Support CTA Settings --}}
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'phone', 'size' => 18])
                            إعدادات صندوق الدعم والتواصل السفلي
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">شارة الصندوق (Badge)</label>
                            <input type="text" name="faq_cta_badge" class="ap-input"
                                value="{{ old('faq_cta_badge', setting('faq.cta_badge', 'ما زلت تبحث عن إجابة؟')) }}">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">عنوان صندوق الدعم <span>*</span></label>
                            <input type="text" name="faq_cta_title" class="ap-input" required
                                value="{{ old('faq_cta_title', setting('faq.cta_title', 'فريقنا مستعد لمساعدتك دائماً')) }}">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">وصف صندوق الدعم</label>
                            <textarea name="faq_cta_desc" class="ap-textarea" rows="3">{{ old('faq_cta_desc', setting('faq.cta_desc', 'تواصل مع فريق الدعم لدينا للحصول على استشارة مجانية وإجابات دقيقة لجميع استفساراتك العقارية في بني سويف ومصر.')) }}</textarea>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">نص زر التواصل <span>*</span></label>
                            <input type="text" name="faq_cta_btn_text" class="ap-input" required
                                value="{{ old('faq_cta_btn_text', setting('faq.cta_btn_text', 'تواصل معنا الآن')) }}">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">رابط زر التواصل <span>*</span></label>
                            <input type="text" name="faq_cta_btn_url" class="ap-input" required
                                value="{{ old('faq_cta_btn_url', setting('faq.cta_btn_url', '/ar/contact-us')) }}">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">نص زر الواتساب <span>*</span></label>
                            <input type="text" name="faq_cta_whatsapp_text" class="ap-input" required
                                value="{{ old('faq_cta_whatsapp_text', setting('faq.cta_whatsapp_text', 'محادثة واتساب سريعة')) }}">
                            <p class="ap-help">رقم الواتساب يُجلب تلقائياً من إعدادات التواصل العامة.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="ap-btn ap-btn--primary">
                    @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                    حفظ إعدادات الصفحة
                </button>
            </div>
        </form>
    @endif
@endsection
