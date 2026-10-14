@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? 'أدخل بيانات المشروع بالعربية مع المرافق والموقع الجغرافي')

@section('page_actions')
    <a href="{{ admin_route('admin.projects.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'x', 'size' => 16]) رجوع للقائمة
    </a>
@endsection

@section('content')
    <form method="post"
        action="{{ $project->exists ? admin_route('admin.projects.update', $project) : admin_route('admin.projects.store') }}">
        @csrf
        @if ($project->exists)
            @method('put')
        @endif

        <div class="ap-grid ap-grid--3">
            <div style="grid-column: span 2;">
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'building', 'size' => 18])
                            البيانات الأساسية
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label" for="reference_code">كود المشروع</label>
                                <input type="text" id="reference_code" name="reference_code" class="ap-input"
                                    value="{{ old('reference_code', $project->reference_code) }}" placeholder="PRJ-2025-XXXXXX">
                                <div class="ap-help">اتركه فارغاً ليتم توليده تلقائياً بصيغة PRJ-السنة-XXXXXX.</div>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="investor_name">اسم المستثمر</label>
                                <input type="text" id="investor_name" name="investor_name" class="ap-input"
                                    value="{{ old('investor_name', $project->investor_name) }}" placeholder="شركة التطوير العقاري…">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="location_id">الموقع / المنطقة</label>
                                <select id="location_id" name="location_id" class="ap-select">
                                    <option value="">— بدون تحديد —</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" @selected((string) old('location_id', $project->location_id) === (string) $location->id)>
                                            {{ $location->translation('ar')?->name ?? '#' . $location->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="status">حالة النشر <span>*</span></label>
                                <select id="status" name="status" class="ap-select" required>
                                    @foreach ($statusLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $project->status ?? 'draft') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="ap-grid ap-grid--3">
                            <div class="ap-field">
                                <label class="ap-label" for="blocks_count">عدد البلوكات</label>
                                <input type="number" min="0" id="blocks_count" name="blocks_count" class="ap-input"
                                    value="{{ old('blocks_count', $project->blocks_count) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="flats_count">عدد الشقق</label>
                                <input type="number" min="0" id="flats_count" name="flats_count" class="ap-input"
                                    value="{{ old('flats_count', $project->flats_count) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="floors_count">عدد الأدوار</label>
                                <input type="number" min="0" id="floors_count" name="floors_count" class="ap-input"
                                    value="{{ old('floors_count', $project->floors_count) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="open_sale_date">تاريخ بدء البيع</label>
                                <input type="date" id="open_sale_date" name="open_sale_date" class="ap-input"
                                    value="{{ old('open_sale_date', $project->open_sale_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="completion_date">تاريخ التسليم المتوقع</label>
                                <input type="date" id="completion_date" name="completion_date" class="ap-input"
                                    value="{{ old('completion_date', $project->completion_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-check" for="is_featured">
                                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                        @checked(old('is_featured', $project->is_featured))>
                                    <span>تمييز المشروع في الواجهة</span>
                                </label>
                            </div>
                            {{-- تحديد الموقع والإحداثيات بالخريطة التفاعلية --}}
                            <div style="grid-column: 1 / -1;">
                                @include('admin.partials.map-picker', [
                                    'latId' => 'latitude',
                                    'lngId' => 'longitude',
                                    'latName' => 'latitude',
                                    'lngName' => 'longitude',
                                    'latValue' => old('latitude', $project->latitude),
                                    'lngValue' => old('longitude', $project->longitude),
                                    'containerId' => 'project-map-picker-box',
                                    'mapId' => 'project-leaflet-picker-map',
                                    'defaultLat' => 29.0724,
                                    'defaultLng' => 31.0962,
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'tag', 'size' => 18])
                            الترجمة العربية
                            <span class="ap-badge ap-badge--gold">إلزامية</span>
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        @php $ar = $project->translation('ar'); @endphp
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label" for="ar_name">اسم المشروع <span>*</span></label>
                                <input type="text" id="ar_name" name="translations[ar][name]" class="ap-input" required
                                    value="{{ old('translations.ar.name', $ar?->name) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="ar_slug">الرابط اللطيف (Slug)</label>
                                <input type="text" id="ar_slug" name="translations[ar][slug]" class="ap-input"
                                    value="{{ old('translations.ar.slug', $ar?->slug) }}">
                                <div class="ap-help">يتم توليده تلقائياً من الاسم عند تركه فارغاً.</div>
                            </div>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label" for="ar_short">وصف مختصر</label>
                            <input type="text" id="ar_short" name="translations[ar][short_description]" class="ap-input"
                                maxlength="500" value="{{ old('translations.ar.short_description', $ar?->short_description) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label" for="ar_description">الوصف الكامل</label>
                            <textarea id="ar_description" name="translations[ar][description]" class="ap-textarea">{{ old('translations.ar.description', $ar?->description) }}</textarea>
                        </div>
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label" for="ar_seo_title">عنوان SEO</label>
                                <input type="text" id="ar_seo_title" name="translations[ar][seo_title]" class="ap-input"
                                    value="{{ old('translations.ar.seo_title', $ar?->seo_title) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="ar_seo_description">وصف SEO</label>
                                <input type="text" id="ar_seo_description" name="translations[ar][seo_description]"
                                    class="ap-input" value="{{ old('translations.ar.seo_description', $ar?->seo_description) }}">
                            </div>
                        </div>
                    </div>
                </div>

                @if (config('locales.admin_english_enabled'))
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'external', 'size' => 18])
                            الترجمة الإنجليزية (اختيارية)
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        @php $en = $project->translation('en'); @endphp
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label" for="en_name">Project name</label>
                                <input type="text" id="en_name" name="translations[en][name]" class="ap-input"
                                    value="{{ old('translations.en.name', $en?->name) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="en_slug">Slug</label>
                                <input type="text" id="en_slug" name="translations[en][slug]" class="ap-input"
                                    value="{{ old('translations.en.slug', $en?->slug) }}">
                            </div>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label" for="en_short">Short description</label>
                            <input type="text" id="en_short" name="translations[en][short_description]" class="ap-input"
                                maxlength="500" value="{{ old('translations.en.short_description', $en?->short_description) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label" for="en_description">Description</label>
                            <textarea id="en_description" name="translations[en][description]" class="ap-textarea">{{ old('translations.en.description', $en?->description) }}</textarea>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 18])
                            المرافق
                        </h2>
                        <span class="ap-card__subtitle">{{ $amenities->count() }} مرفق</span>
                    </div>
                    <div class="ap-card__body">
                        @php $selectedAmenities = old('amenities', $project->amenities->pluck('id')->all()); @endphp
                        @forelse ($amenities as $amenity)
                            <label class="ap-check" for="amenity_{{ $amenity->id }}" style="margin-bottom: 8px;">
                                <input type="checkbox" id="amenity_{{ $amenity->id }}" name="amenities[]"
                                    value="{{ $amenity->id }}" @checked(in_array($amenity->id, $selectedAmenities, false))>
                                <span>{{ $amenity->translation('ar')?->name ?? $amenity->key }}</span>
                            </label>
                        @empty
                            <p class="text-muted mb-0">لا توجد مرافق مسجّلة بعد.</p>
                        @endforelse
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__body">
                        <button type="submit" class="ap-btn ap-btn--gold ap-btn--block">
                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                            {{ $project->exists ? 'حفظ التعديلات' : 'إنشاء المشروع' }}
                        </button>
                        <a href="{{ admin_route('admin.projects.index') }}" class="ap-btn ap-btn--ghost ap-btn--block"
                            style="margin-top: 8px;">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
