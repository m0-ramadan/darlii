@extends('layouts.admin')

@php
    $isEdit = $property->exists;
    $ar = $property->translations->firstWhere('locale', 'ar') ?? new \App\Models\PropertyTranslation(['locale' => 'ar']);
    $en = $property->translations->firstWhere('locale', 'en') ?? new \App\Models\PropertyTranslation(['locale' => 'en']);
    $oldValue = fn (string $key, $fallback = null) => old($key, $fallback);
    $selectedAmenities = old('amenities', $property->exists ? $property->amenities->pluck('id')->all() : []);
@endphp

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? 'كل بيانات العقار: الموقع، السعر، التفاصيل، الصور، والإسناد')

@section('page_actions')
    <a href="{{ admin_route('admin.properties.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع للقائمة</a>
    @if ($isEdit)
        <a href="{{ admin_route('admin.properties.stats', $property) }}" class="ap-btn ap-btn--ghost ap-btn--sm">الإحصائيات</a>
    @endif
@endsection

@section('content')
    <form method="post" enctype="multipart/form-data"
        action="{{ $isEdit ? admin_route('admin.properties.update', $property) : admin_route('admin.properties.store') }}">
        @csrf
        @if ($isEdit)
            @method('put')
        @endif

        <div class="ap-grid ap-grid--2" style="align-items: start;">
            {{-- ------------------------------------------------ البيانات الأساسية --}}
            <div>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 18]) البيانات الأساسية</h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">كود العقار</label>
                                <input type="text" name="reference_code" class="ap-input"
                                    value="{{ $oldValue('reference_code', $property->reference_code) }}"
                                    placeholder="يُولَّد تلقائياً إذا تُرك فارغاً">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الغرض <span>*</span></label>
                                <select name="purpose" class="ap-select" required>
                                    <option value="sale" @selected($oldValue('purpose', $property->purpose) === 'sale')>بيع</option>
                                    <option value="rent" @selected($oldValue('purpose', $property->purpose) === 'rent')>إيجار</option>
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الحالة <span>*</span></label>
                                <select name="status" class="ap-select" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected($oldValue('status', $property->status?->value) === $status->value)>
                                            {{ status_label($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">نوع السعر / سياسة التسعير <span>*</span></label>
                                <select name="price_type" id="propertyPriceTypeSelect" class="ap-select" required>
                                    <option value="fixed" @selected((string) $oldValue('price_type', $property->price_type) === 'fixed')>
                                        محدد (سعر نهائي وثابت)
                                    </option>
                                    <option value="negotiable" @selected((string) $oldValue('price_type', $property->price_type) === 'negotiable')>
                                        قابل للتفاوض (يوجد مجال للتفاوض)
                                    </option>
                                    <option value="contact" @selected((string) $oldValue('price_type', $property->price_type) === 'contact')>
                                        يتم تحديده من خلال التواصل (السعر عند الطلب)
                                    </option>
                                </select>
                                <small class="text-muted" style="font-size: 0.8rem; display: block; margin-top: 4px;">
                                    حدد ما إذا كان السعر معلناً وثابتاً، أو قابلاً للتفاوض، أو مخفياً يظهر "السعر عند التواصل".
                                </small>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">العملة <span>*</span></label>
                                <select name="currency_id" class="ap-select" required>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}" @selected((string) $oldValue('currency_id', $property->currency_id) === (string) $currency->id)>
                                            {{ $currency->code }} — {{ $currency->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field" id="propertyPriceField">
                                <label class="ap-label">السعر <span id="propertyPriceRequiredAsterisk">*</span></label>
                                <input type="number" step="0.01" min="0" name="price" id="propertyPriceInput" class="ap-input" required
                                    value="{{ $oldValue('price', $property->price) }}">
                                <small id="propertyPriceHint" class="text-muted" style="font-size: 0.8rem; display: block; margin-top: 4px;"></small>
                            </div>
                            <div class="ap-field ap-col-span-full" style="grid-column: 1 / -1; background: rgba(245, 158, 11, 0.08); border: 1px dashed rgba(217, 119, 6, 0.45); padding: 14px; border-radius: 8px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; flex-wrap: wrap; gap: 8px;">
                                    <label class="ap-label" style="margin: 0; color: #b45309; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        ملاحظات سرية خاصة بالسعر والتفاوض (للإدارة والوسطاء فقط)
                                    </label>
                                    <span class="ap-badge ap-badge--warning" style="font-size: 11px;">
                                        🔒 سرية تماماً - لا تظهر للعميل المشتري أو الزوار نهائياً
                                    </span>
                                </div>
                                <textarea name="price_notes" class="ap-textarea" rows="2" placeholder="اكتب ما تم الاتفاق عليه بينك وبين العميل بخصوص السعر والتفاوض (مثال: العميل متفق ينزل لـ 1,800,000 كاش عند الجدية، أو السعر يشمل الفرش، أو عمولة الوسيط 2.5%...)">{{ $oldValue('price_notes', $property->price_notes) }}</textarea>
                                <div style="font-size: 0.78rem; color: #92400e; margin-top: 4px;">
                                    تساعد مسؤولي المبيعات والوسطاء على معرفة حدود التفاوض المتفق عليها مع المالك دون الحاجة للرجوع إليه في كل مكالمة.
                                </div>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">تاريخ الانتهاء</label>
                                <input type="date" name="expires_at" class="ap-input"
                                    value="{{ $oldValue('expires_at', $property->expires_at?->format('Y-m-d')) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">التصنيف</label>
                                <select name="category_id" class="ap-select">
                                    <option value="">— بدون —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) $oldValue('category_id', $property->category_id) === (string) $category->id)>
                                            {{ $category->translation('ar')?->name ?? $category->key }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">نوع العقار</label>
                                <select name="property_type_id" class="ap-select">
                                    <option value="">— بدون —</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}" @selected((string) $oldValue('property_type_id', $property->property_type_id) === (string) $type->id)>
                                            {{ $type->translation('ar')?->name ?? $type->key }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الموقع / المنطقة</label>
                                <select name="location_id" class="ap-select">
                                    <option value="">— بدون —</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" @selected((string) $oldValue('location_id', $property->location_id) === (string) $location->id)>
                                            {{ $location->translation('ar')?->name ?? ('#' . $location->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">المشروع</label>
                                <select name="project_id" class="ap-select">
                                    <option value="">— بدون —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}" @selected((string) $oldValue('project_id', $property->project_id) === (string) $project->id)>
                                            {{ $project->translation('ar')?->name ?? $project->reference_code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <label class="ap-check" style="margin-top: 6px;">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" @checked($oldValue('is_featured', $property->is_featured))>
                            عقار مميز (يظهر في الصفحة الرئيسية)
                        </label>
                    </div>
                </div>

                {{-- ------------------------------------------------------- الإسناد --}}
                @can('property.assign')
                    <div class="ap-card">
                        <div class="ap-card__head">
                            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) الإسناد والملكية</h2>
                        </div>
                        <div class="ap-card__body">
                            <p class="text-muted" style="font-size: 12.5px;">
                                العقار يجب أن يكون مُسنداً لمسؤول أو سمسار. الأدمن يستطيع الإسناد لأي مستخدم، والمسؤول يُسند فقط لسماسرته.
                            </p>
                            <div class="ap-grid ap-grid--2">
                                <div class="ap-field">
                                    <label class="ap-label">المالك / السمسار</label>
                                    <select name="owner_id" class="ap-select">
                                        <option value="">— بدون —</option>
                                        @foreach ($assignableUsers as $assignee)
                                            <option value="{{ $assignee->id }}" @selected((string) $oldValue('owner_id', $property->owner_id) === (string) $assignee->id)>
                                                {{ $assignee->name }} — {{ user_role_label($assignee->primary_role_slug) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ap-field">
                                    <label class="ap-label">المسؤول المتابع</label>
                                    <select name="managed_by" class="ap-select">
                                        <option value="">— بدون —</option>
                                        @foreach ($managers as $manager)
                                            <option value="{{ $manager->id }}" @selected((string) $oldValue('managed_by', $property->managed_by) === (string) $manager->id)>
                                                {{ $manager->name }} — {{ user_role_label($manager->primary_role_slug) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if ($isEdit)
                                <div class="ap-alert ap-alert--info" style="margin-bottom: 0;">
                                    <div>
                                        <strong>حالة الإسناد:</strong> {{ $property->assignmentLabel() }}
                                        @if ($property->last_assigned_at)
                                            — آخر إسناد {{ $property->last_assigned_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endcan

                {{-- ------------------------------------------------------- الصور --}}
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'photo', 'size' => 18]) صور العقار</h2>
                        <span class="ap-badge">{{ $property->exists ? $property->media->count() : 0 }} صورة</span>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">إضافة صور</label>
                            <input type="file" name="images[]" class="ap-input" accept="image/*" multiple>
                            <div class="ap-help">الصيغ: jpg, png, webp — أقصى حجم {{ setting('storage.max_upload_mb', 8) }} ميجابايت للصورة.</div>
                        </div>

                        @if ($isEdit && $property->media->isNotEmpty())
                            <div class="ap-thumb-grid">
                                @foreach ($property->media as $media)
                                    <div class="ap-thumb">
                                        <img src="{{ str_starts_with((string) $media->path, 'http') ? $media->path : asset('storage/' . ltrim($media->path, '/')) }}"
                                            alt="" loading="lazy"
                                            onerror="this.src='{{ $property->placeholderUrl('ar') }}'">
                                        @if ($media->is_primary)
                                            <span class="ap-badge ap-badge--gold ap-thumb__badge">رئيسية</span>
                                        @endif
                                        <div class="ap-thumb__actions">
                                            @if (!$media->is_primary)
                                                <button type="submit" form="mediaPrimary{{ $media->id }}"
                                                    class="ap-btn ap-btn--ghost ap-btn--sm">تعيين رئيسية</button>
                                            @endif
                                            <button type="submit" form="mediaDelete{{ $media->id }}"
                                                class="ap-btn ap-btn--danger ap-btn--sm">حذف</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif (!$isEdit)
                            <div class="ap-alert ap-alert--info" style="margin: 0;">سيتم رفع الصور بعد حفظ العقار أول مرة.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ------------------------------------------------------- التفاصيل --}}
            <div>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'tag', 'size' => 18]) التفاصيل والمساحات</h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-grid ap-grid--3">
                            <div class="ap-field">
                                <label class="ap-label">غرف النوم</label>
                                <input type="number" min="0" name="bedrooms" class="ap-input" value="{{ $oldValue('bedrooms', $property->bedrooms) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الحمامات</label>
                                <input type="number" min="0" name="bathrooms" class="ap-input" value="{{ $oldValue('bathrooms', $property->bathrooms) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">أماكن الانتظار</label>
                                <input type="number" min="0" name="parking_spaces" class="ap-input" value="{{ $oldValue('parking_spaces', $property->parking_spaces) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">المساحة</label>
                                <input type="number" step="0.01" min="0" name="area" class="ap-input" value="{{ $oldValue('area', $property->area) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">وحدة المساحة</label>
                                <select name="area_unit" class="ap-select">
                                    @foreach (['m2' => 'متر مربع', 'ft2' => 'قدم مربع', 'acre' => 'فدان', 'hectare' => 'هكتار'] as $unit => $label)
                                        <option value="{{ $unit }}" @selected($oldValue('area_unit', $property->area_unit ?? 'm2') === $unit)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">مساحة الأرض</label>
                                <input type="number" step="0.01" min="0" name="land_area" class="ap-input" value="{{ $oldValue('land_area', $property->land_area) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">عدد الأدوار</label>
                                <input type="number" min="0" name="floors_count" class="ap-input" value="{{ $oldValue('floors_count', $property->floors_count) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الدور</label>
                                <input type="number" name="floor_number" class="ap-input" value="{{ $oldValue('floor_number', $property->floor_number) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">سنة البناء</label>
                                <input type="number" min="1800" max="2100" name="year_built" class="ap-input" value="{{ $oldValue('year_built', $property->year_built) }}">
                            </div>
                        </div>

                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">خط العرض (Latitude)</label>
                                <input type="text" name="latitude" class="ap-input" value="{{ $oldValue('latitude', $property->latitude) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">خط الطول (Longitude)</label>
                                <input type="text" name="longitude" class="ap-input" value="{{ $oldValue('longitude', $property->longitude) }}">
                            </div>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">المرافق والخدمات</label>
                            <div class="ap-grid ap-grid--3">
                                @foreach ($amenities as $amenity)
                                    <label class="ap-check">
                                        <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                            @checked(in_array($amenity->id, $selectedAmenities))>
                                        {{ $amenity->translation('ar')?->name ?? $amenity->key }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">ملاحظات الإدارة (لا تظهر للزوار)</label>
                            <textarea name="admin_note" class="ap-textarea" rows="3">{{ $oldValue('admin_note', $property->admin_note) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'edit', 'size' => 18]) المحتوى (عربي / إنجليزي)</h2>
                    </div>
                    <div class="ap-card__body">
                        <h3 style="font-size: 14px;">العربية</h3>
                        <div class="ap-field">
                            <label class="ap-label">اسم العقار (عربي) <span>*</span></label>
                            <input type="text" name="translations[ar][name]" class="ap-input" required
                                value="{{ old('translations.ar.name', $ar->name) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">الرابط اللطيف (Slug)</label>
                            <input type="text" name="translations[ar][slug]" class="ap-input" value="{{ old('translations.ar.slug', $ar->slug) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">وصف مختصر</label>
                            <input type="text" name="translations[ar][short_description]" class="ap-input"
                                value="{{ old('translations.ar.short_description', $ar->short_description) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">الوصف الكامل</label>
                            <textarea name="translations[ar][description]" class="ap-textarea" rows="5">{{ old('translations.ar.description', $ar->description) }}</textarea>
                        </div>
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">العنوان التفصيلي</label>
                                <input type="text" name="translations[ar][address_text]" class="ap-input"
                                    value="{{ old('translations.ar.address_text', $ar->address_text) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">عنوان SEO</label>
                                <input type="text" name="translations[ar][seo_title]" class="ap-input"
                                    value="{{ old('translations.ar.seo_title', $ar->seo_title) }}">
                            </div>
                        </div>

                        <hr style="margin: 18px 0; border: 0; border-top: 1px solid var(--ap-border);">

                        <h3 style="font-size: 14px;">English</h3>
                        <div class="ap-field">
                            <label class="ap-label">Property name (EN)</label>
                            <input type="text" name="translations[en][name]" class="ap-input" value="{{ old('translations.en.name', $en->name) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Slug (EN)</label>
                            <input type="text" name="translations[en][slug]" class="ap-input" value="{{ old('translations.en.slug', $en->slug) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Short description (EN)</label>
                            <input type="text" name="translations[en][short_description]" class="ap-input"
                                value="{{ old('translations.en.short_description', $en->short_description) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Full description (EN)</label>
                            <textarea name="translations[en][description]" class="ap-textarea" rows="4">{{ old('translations.en.description', $en->description) }}</textarea>
                        </div>
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">Address (EN)</label>
                                <input type="text" name="translations[en][address_text]" class="ap-input"
                                    value="{{ old('translations.en.address_text', $en->address_text) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">SEO title (EN)</label>
                                <input type="text" name="translations[en][seo_title]" class="ap-input"
                                    value="{{ old('translations.en.seo_title', $en->seo_title) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__body d-flex gap-2 flex-wrap">
                        <button class="ap-btn ap-btn--gold">
                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                            {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء العقار' }}
                        </button>
                        <a href="{{ admin_route('admin.properties.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- نماذج مساعدة للصور (خارج النموذج الرئيسي لتجنّب التداخل) --}}
    @if ($isEdit)
        @foreach ($property->media as $media)
            @if (!$media->is_primary)
                <form id="mediaPrimary{{ $media->id }}" method="post"
                    action="{{ admin_route('admin.properties.media.primary', [$property, $media]) }}">
                    @csrf
                </form>
            @endif
            <form id="mediaDelete{{ $media->id }}" method="post"
                action="{{ admin_route('admin.properties.media.destroy', [$property, $media]) }}"
                data-confirm="حذف هذه الصورة نهائياً؟">
                @csrf
                @method('delete')
            </form>
        @endforeach
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var priceTypeSelect = document.getElementById('propertyPriceTypeSelect');
            var priceInput = document.getElementById('propertyPriceInput');
            var priceAsterisk = document.getElementById('propertyPriceRequiredAsterisk');
            var priceHint = document.getElementById('propertyPriceHint');

            function syncPriceState() {
                if (!priceTypeSelect || !priceInput) return;
                var val = priceTypeSelect.value;
                if (val === 'contact') {
                    priceInput.removeAttribute('required');
                    if (priceAsterisk) priceAsterisk.style.display = 'none';
                    if (priceHint) {
                        priceHint.textContent = 'عند اختيار "يتم تحديده من خلال التواصل"، سيظهر للزوار "السعر عند التواصل" ولن يظهر أي رقم.';
                        priceHint.style.color = '#d97706';
                    }
                } else if (val === 'negotiable') {
                    priceInput.setAttribute('required', 'required');
                    if (priceAsterisk) priceAsterisk.style.display = 'inline';
                    if (priceHint) {
                        priceHint.textContent = 'سيظهر السعر للزوار ومعه شارة مميزة "قابل للتفاوض".';
                        priceHint.style.color = '#059669';
                    }
                } else {
                    priceInput.setAttribute('required', 'required');
                    if (priceAsterisk) priceAsterisk.style.display = 'inline';
                    if (priceHint) {
                        priceHint.textContent = 'سعر محدد وثابت لا يقبل التفاوض.';
                        priceHint.style.color = '#64748b';
                    }
                }
            }

            if (priceTypeSelect) {
                priceTypeSelect.addEventListener('change', syncPriceState);
                syncPriceState();
            }
        });
    </script>
@endsection
