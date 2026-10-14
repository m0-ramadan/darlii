@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? '')

@section('page_actions')
    @can('property.update-any')
        <a href="{{ admin_route('admin.properties.edit', $property) }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 16]) تعديل
        </a>
    @endcan
    <a href="{{ admin_route('admin.properties.stats', $property) }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 16]) الإحصائيات
    </a>
    <a href="{{ admin_route('admin.properties.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a>
@endsection

@section('content')
    @php
        $ar = $property->translation('ar');
        $en = $property->translation('en');
    @endphp

    {{-- --------------------------------------------------------- ملخص سريع --}}
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'eye', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المشاهدات</div>
                <div class="ap-stat__value">{{ number_format($stats['views']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">العملاء المحتملون</div>
                <div class="ap-stat__value">{{ number_format($stats['leads']) }}</div>
                <div class="ap-stat__hint">{{ $stats['leads_new'] }} جديد</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المفضلة / التقييمات</div>
                <div class="ap-stat__value">{{ number_format($stats['favorites']) }} / {{ $stats['reviews'] }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'money', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">سعر المتر</div>
                <div class="ap-stat__value" style="font-size: 18px;">{{ money_eg($stats['price_per_meter']) }}</div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--2" style="align-items: start;">
        <div>
            {{-- ------------------------------------------------------ الصور --}}
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'photo', 'size' => 18]) الصور</h2>
                    <span class="ap-badge">{{ $property->media->count() }}</span>
                </div>
                <div class="ap-card__body">
                    @if ($property->media->isEmpty())
                        <div class="ap-empty">
                            @include('admin.partials.icon', ['iconName' => 'photo', 'size' => 38])
                            <p>لا توجد صور لهذا العقار.</p>
                        </div>
                    @else
                        <img src="{{ $property->primaryImageUrl('ar') }}" alt="" style="width: 100%; border-radius: 12px; margin-bottom: 12px; max-height: 320px; object-fit: cover;">
                        <div class="ap-thumb-grid">
                            @foreach ($property->media as $media)
                                <div class="ap-thumb">
                                    <img src="{{ str_starts_with((string) $media->path, 'http') ? $media->path : asset('storage/' . ltrim($media->path, '/')) }}"
                                        alt="" loading="lazy" onerror="this.src='{{ $property->placeholderUrl('ar') }}'">
                                    @if ($media->is_primary)
                                        <span class="ap-badge ap-badge--gold ap-thumb__badge">رئيسية</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ------------------------------------------------ التخطيط الهندسي للشقة --}}
            @php
                $adminFloorPlans = $property->getFloorPlans();
            @endphp
            @if (!empty($adminFloorPlans))
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            <svg style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-left: 6px; color: #b45309;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                <line x1="9" y1="21" x2="9" y2="9"></line>
                            </svg>
                            التخطيط الهندسي للشقة ({{ count($adminFloorPlans) }})
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            @foreach ($adminFloorPlans as $fp)
                                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: rgba(248, 250, 252, 0.8);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                                        <strong style="color: #1e293b;">📐 {{ $fp['name'] ?? 'مخطط هندسي' }}</strong>
                                        <div style="display: flex; gap: 10px; font-size: 12px; color: #64748b;">
                                            @if(!empty($fp['bedrooms'])) <span>{{ $fp['bedrooms'] }} غرف</span> @endif
                                            @if(!empty($fp['bathrooms'])) <span>{{ $fp['bathrooms'] }} حمام</span> @endif
                                            @if(!empty($fp['area'])) <span>{{ $fp['area'] }} م²</span> @endif
                                        </div>
                                    </div>
                                    @if(!empty($fp['description']))
                                        <p style="font-size: 13px; color: #475569; margin-bottom: 8px;">{{ $fp['description'] }}</p>
                                    @endif
                                    @if(!empty($fp['image_url']) || !empty($fp['image']))
                                        @php
                                            $adminPlanImg = !empty($fp['image_url']) ? $fp['image_url'] : asset('storage/' . ltrim($fp['image'], '/'));
                                        @endphp
                                        <a href="{{ $adminPlanImg }}" target="_blank" style="display: block; text-align: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px;">
                                            <img src="{{ $adminPlanImg }}" alt="{{ $fp['name'] ?? 'Floor plan' }}" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- -------------------------------------------------- التفاصيل --}}
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 18]) التفاصيل</h2>
                </div>
                <div class="ap-card__body">
                    <table class="ap-table">
                        <tbody>
                            <tr><th style="width: 45%;">كود العقار</th><td><code>{{ $property->reference_code }}</code></td></tr>
                            <tr><th>الحالة</th><td><span class="ap-badge ap-badge--{{ status_color($property->status) }}">{{ status_label($property->status) }}</span></td></tr>
                            <tr><th>الغرض</th><td>{{ $property->purpose === 'rent' ? 'إيجار' : 'بيع' }}</td></tr>
                            <tr>
                                <th>السعر ونوع التسعير</th>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <span class="fw-bold">{{ money_eg($property->price) }}</span>
                                        @if ($property->price_type === 'negotiable')
                                            <span class="ap-badge ap-badge--info">قابل للتفاوض</span>
                                        @elseif ($property->price_type === 'contact')
                                            <span class="ap-badge ap-badge--warning">يتم تحديده من خلال التواصل</span>
                                        @else
                                            <span class="ap-badge ap-badge--secondary">محدد (ثابت)</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr><th>التصنيف</th><td>{{ $property->category?->translation('ar')?->name ?? '—' }}</td></tr>
                            <tr><th>الموقع</th><td>{{ $property->location?->translation('ar')?->name ?? '—' }}</td></tr>
                            <tr><th>المشروع</th><td>{{ $property->project?->translation('ar')?->name ?? '—' }}</td></tr>
                            <tr><th>غرف النوم / الحمامات</th><td>{{ $property->bedrooms ?? '—' }} / {{ $property->bathrooms ?? '—' }}</td></tr>
                            <tr><th>المساحة</th><td>{{ $property->area ? number_format((float) $property->area, 2) . ' ' . $property->area_unit : '—' }}</td></tr>
                            <tr><th>الدور / الأدوار</th><td>{{ $property->floor_number ?? '—' }} / {{ $property->floors_count ?? '—' }}</td></tr>
                            <tr><th>سنة البناء</th><td>{{ $property->year_built ?? '—' }}</td></tr>
                            <tr><th>الإحداثيات</th><td>{{ $property->latitude ?? '—' }}, {{ $property->longitude ?? '—' }}</td></tr>
                            <tr><th>مميز</th><td>{!! $property->is_featured ? '<span class="ap-badge ap-badge--gold">نعم</span>' : 'لا' !!}</td></tr>
                            <tr><th>تاريخ النشر</th><td>{{ $property->published_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                            <tr><th>ينتهي في</th><td>{{ $property->expires_at?->format('Y-m-d') ?? '—' }}</td></tr>
                            <tr><th>أُنشئ</th><td>{{ $property->created_at?->format('Y-m-d H:i') }}</td></tr>
                        </tbody>
                    </table>

                    @if ($property->price_notes)
                        <div class="ap-alert" style="margin-top: 14px; margin-bottom: 0; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 8px; padding: 12px 16px;">
                            <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                <svg style="width: 16px; height: 16px; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <span>ملاحظات سرية خاصة بالسعر والتفاوض (بين الإدارة والعميل):</span>
                            </div>
                            <div style="white-space: pre-line; color: #78350f; font-size: 0.9rem;">{{ $property->price_notes }}</div>
                        </div>
                    @endif

                    @if ($property->admin_note)
                        <div class="ap-alert ap-alert--warning" style="margin-top: 14px; margin-bottom: 0;">
                            <div><strong>ملاحظات الإدارة:</strong> {{ $property->admin_note }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ------------------------------------------- المرافق والإسناد --}}
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) الإسناد والملكية</h2>
                </div>
                <div class="ap-card__body">
                    <table class="ap-table">
                        <tbody>
                            <tr><th style="width: 45%;">المالك / السمسار</th><td>{{ $property->owner?->name ?? '—' }}</td></tr>
                            <tr><th>المسؤول المتابع</th><td>{{ $property->manager?->name ?? '—' }}</td></tr>
                            <tr><th>منشئ العقار</th><td>{{ $property->creator?->name ?? '—' }}</td></tr>
                            <tr><th>حالة الإسناد</th><td><span class="ap-badge ap-badge--{{ $property->assignment_status === 'assigned' ? 'success' : 'warning' }}">{{ $property->assignmentLabel() }}</span></td></tr>
                            <tr><th>آخر إسناد</th><td>{{ $property->last_assigned_at?->diffForHumans() ?? '—' }}</td></tr>
                        </tbody>
                    </table>

                    @can('property.assign')
                        <form method="post" action="{{ admin_route('admin.properties.assign', $property) }}" class="ap-grid ap-grid--3" style="margin-top: 14px;">
                            @csrf
                            <div class="ap-field" style="margin-bottom: 0;">
                                <label class="ap-label">المالك / السمسار</label>
                                <select name="owner_id" class="ap-select ap-select--sm" required>
                                    @foreach ($assignableUsers as $assignee)
                                        <option value="{{ $assignee->id }}" @selected($property->owner_id === $assignee->id)>{{ $assignee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field" style="margin-bottom: 0;">
                                <label class="ap-label">المسؤول المتابع</label>
                                <select name="managed_by" class="ap-select ap-select--sm">
                                    <option value="">— نفس المالك —</option>
                                    @foreach ($managers as $manager)
                                        <option value="{{ $manager->id }}" @selected($property->managed_by === $manager->id)>{{ $manager->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field" style="margin-bottom: 0;">
                                <label class="ap-label">&nbsp;</label>
                                <button class="ap-btn ap-btn--sm ap-btn--block">إسناد العقار</button>
                            </div>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        <div>
            {{-- ------------------------------------------------- الإجراءات --}}
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'toggle', 'size' => 18]) إجراءات سريعة</h2>
                </div>
                <div class="ap-card__body" style="display: grid !important; grid-template-columns: 1fr 1fr 1fr !important; gap: 8px !important; padding: 12px 10px !important;">
                    @can('property.approve')
                        <form method="post" action="{{ admin_route('admin.properties.approve', $property) }}" style="margin: 0; min-width: 0; width: 100%;">
                            @csrf
                            <button class="ap-btn ap-btn--success" style="width: 100% !important; min-width: 0 !important; font-size: 12px !important; padding: 9px 4px !important; white-space: nowrap !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; font-weight: 700 !important; border-radius: 8px !important;">
                                اعتماد ونشر العقار
                            </button>
                        </form>
                    @endcan
                    @can('property.feature')
                        <form method="post" action="{{ admin_route('admin.properties.feature', $property) }}" style="margin: 0; min-width: 0; width: 100%;">
                            @csrf
                            <button class="ap-btn ap-btn--gold" style="width: 100% !important; min-width: 0 !important; font-size: 12px !important; padding: 9px 4px !important; white-space: nowrap !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; font-weight: 700 !important; border-radius: 8px !important;">
                                {{ $property->is_featured ? 'إلغاء التمييز' : 'تمييز العقار' }}
                            </button>
                        </form>
                    @endcan
                    @can('property.delete')
                        <form method="post" action="{{ admin_route('admin.properties.destroy', $property) }}"
                            data-confirm="حذف العقار نهائياً؟" style="margin: 0; min-width: 0; width: 100%;">
                            @csrf
                            @method('delete')
                            <button class="ap-btn ap-btn--danger" style="width: 100% !important; min-width: 0 !important; font-size: 12px !important; padding: 9px 4px !important; white-space: nowrap !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; font-weight: 700 !important; border-radius: 8px !important;">
                                حذف العقار
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            @can('property.approve')
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'x', 'size' => 18]) رفض العقار</h2>
                    </div>
                    <div class="ap-card__body">
                        <form method="post" action="{{ admin_route('admin.properties.reject', $property) }}">
                            @csrf
                            <div class="ap-field">
                                <label class="ap-label">سبب الرفض <span>*</span></label>
                                <textarea name="reason" class="ap-textarea" rows="3" required placeholder="وضّح سبب الرفض ليصل للسمسار…"></textarea>
                            </div>
                            <button class="ap-btn ap-btn--danger ap-btn--sm">رفض العقار</button>
                        </form>
                    </div>
                </div>
            @endcan

            {{-- ---------------------------------------------- المحتوى --}}
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'file-text', 'size' => 18]) المحتوى</h2>
                </div>
                <div class="ap-card__body">
                    <h3 style="font-size: 13.5px;">عربي</h3>
                    <p class="fw-bold">{{ $ar?->name ?? '—' }}</p>
                    <p class="text-muted" style="font-size: 12.5px;">{{ $ar?->short_description ?? '' }}</p>
                    <div style="font-size: 13px; white-space: pre-line;">{{ \Illuminate\Support\Str::limit(strip_tags((string) $ar?->description), 600) }}</div>

                    @if ($en)
                        <hr style="margin: 14px 0; border: 0; border-top: 1px solid var(--ap-border);">
                        <h3 style="font-size: 13.5px;">English</h3>
                        <p class="fw-bold">{{ $en->name }}</p>
                        <div style="font-size: 13px; white-space: pre-line;">{{ \Illuminate\Support\Str::limit(strip_tags((string) $en->description), 400) }}</div>
                    @endif

                    @if ($property->amenities->isNotEmpty())
                        <hr style="margin: 14px 0; border: 0; border-top: 1px solid var(--ap-border);">
                        <h3 style="font-size: 13.5px;">المرافق</h3>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($property->amenities as $amenity)
                                <span class="ap-badge">{{ $amenity->translation('ar')?->name ?? $amenity->key }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ----------------------------------------- سجل الحالة --}}
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 18]) سجل تغييرات الحالة</h2>
                </div>
                <div class="ap-card__body ap-card__body--tight">
                    <div class="ap-table-wrap">
                        <table class="ap-table">
                            <thead>
                                <tr><th>من</th><th>إلى</th><th>السبب</th><th>التاريخ</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($property->statusHistory->sortByDesc('created_at') as $history)
                                    <tr>
                                        <td>{{ status_label($history->old_status) }}</td>
                                        <td><span class="ap-badge ap-badge--{{ status_color($history->new_status) }}">{{ status_label($history->new_status) }}</span></td>
                                        <td>{{ \Illuminate\Support\Str::limit($history->reason, 60) ?: '—' }}</td>
                                        <td>{{ $history->created_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center">لا يوجد سجل بعد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
