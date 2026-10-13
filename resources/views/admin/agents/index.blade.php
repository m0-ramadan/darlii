@extends('layouts.admin')

@section('title', 'الشركات والوكلاء العقاريين')
@section('page_title', 'الشركات والوكلاء العقاريين')
@section('page_subtitle', 'إدارة وتوثيق شركات ووكلاء العقارات المعتمدين المرتبطين بالمنصة')

@section('page_actions')
    @can('users.create')
        <a href="{{ admin_route('admin.agents.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة وكيل / شركة
        </a>
    @endcan
    <a href="{{ route('agents.index', ['locale' => 'ar']) }}" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'external', 'size' => 16]) صفحة الوكلاء بالموقع
    </a>
@endsection

@section('content')
    {{-- مؤشرات سريعة --}}
    <div class="ap-grid ap-grid--4" style="margin-bottom: 20px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">إجمالي الوكلاء والشركات</div>
                <div class="ap-stat__value">{{ number_format($stats['total']) }}</div>
                <div class="ap-stat__hint">يشمل الشركات والوكلاء المسجلين</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">الوكلاء المعتمدون (موثق)</div>
                <div class="ap-stat__value">{{ number_format($stats['verified']) }}</div>
                <div class="ap-stat__hint">يحملون شارة الاعتماد والتوثيق</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">الحسابات النشطة</div>
                <div class="ap-stat__value">{{ number_format($stats['active']) }}</div>
                <div class="ap-stat__hint">تظهر في دليل الوكلاء بالموقع</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">الشركات والوكالات</div>
                <div class="ap-stat__value">{{ number_format($stats['agencies']) }}</div>
                <div class="ap-stat__hint">الكيانات والوكالات العقارية</div>
            </div>
        </div>
    </div>

    {{-- الفلترة والبحث --}}
    <div class="ap-card" style="margin-bottom: 20px;">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 220px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ request('q') }}" class="ap-input ap-input--sm" placeholder="الاسم، الشركة، البريد أو الهاتف…">
                </div>

                <div class="ap-field">
                    <label class="ap-label">الشركة / الوكالة</label>
                    <select name="agency_id" class="ap-select ap-select--sm">
                        <option value="">كل الشركات</option>
                        @foreach ($agencies as $agency)
                            <option value="{{ $agency->id }}" @selected(request('agency_id') == $agency->id)>{{ $agency->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ap-field">
                    <label class="ap-label">التوثيق والاعتماد</label>
                    <select name="verified" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="1" @selected(request('verified') === '1')>معتمد فقط</option>
                        <option value="0" @selected(request('verified') === '0')>غير معتمد</option>
                    </select>
                </div>

                <div class="ap-field">
                    <label class="ap-label">نوع المسؤول</label>
                    <select name="agent_type" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="individual" @selected(request('agent_type') === 'individual')>شخص / مسؤول فردي</option>
                        <option value="office" @selected(request('agent_type') === 'office')>مكتب عقارات / شركة</option>
                        <option value="broker" @selected(request('agent_type') === 'broker')>سمسار عادي</option>
                    </select>
                </div>

                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="active" @selected(request('status') === 'active')>نشط</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>غير نشط</option>
                    </select>
                </div>

                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ admin_route('admin.agents.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    {{-- جدول الوكلاء والشركات --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 18])
                دليل الوكلاء والشركات
                <span class="ap-badge">{{ number_format($agents->total()) }}</span>
            </h2>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الوكيل / المسؤول</th>
                            <th>نوع المسؤول والكيان</th>
                            <th>المسمى والترخيص</th>
                            <th>بنود التعامل والنسب</th>
                            <th>بيانات التواصل</th>
                            <th>العقارات</th>
                            <th>الاعتماد</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agents as $agent)
                            @php
                                $name = $agent->display_name;
                                $avatarNum = (($agent->id - 1) % 11) + 1;
                                $avatarUrl = $agent->avatar_path ? asset($agent->avatar_path) : asset("storage/avatars/{$avatarNum}.jpg");
                                $propCount = $agent->user?->properties()->count() ?? 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $avatarUrl }}" alt="{{ $name }}" class="ap-avatar ap-avatar--sm" style="object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('themes/homzen/images/darli-placeholder.png') }}';">
                                        <div>
                                            <a href="{{ admin_route('admin.agents.edit', $agent) }}" class="fw-bold">{{ $name }}</a>
                                            <div class="text-muted" dir="ltr" style="font-size: 11px;">{{ $agent->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="ap-badge {{ $agent->agent_type_badge_class }}" style="font-size: 11px;">
                                            {{ $agent->agent_type_label }}
                                        </span>
                                    </div>
                                    @if ($agent->agency)
                                        <div style="margin-top: 4px;"><span class="text-muted" style="font-size: 11px;">تابع لـ: {{ $agent->agency->name }}</span></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size: 13px;">{{ $agent->job_title ?: 'مستشار عقاري' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        @if($agent->license_number) ترخيص: {{ $agent->license_number }} · @endif
                                        خبرة: {{ $agent->years_experience }} سنة
                                    </div>
                                </td>
                                <td>
                                    @if ($agent->contract_terms)
                                        <div title="{{ $agent->contract_terms }}" style="max-width: 200px; font-size: 11px; line-height: 1.4; color: #b45309; background: rgba(245,158,11,0.08); padding: 4px 8px; border-radius: 6px; border: 1px dashed rgba(245,158,11,0.3); cursor: help;">
                                            <span class="fw-bold">📜 بنود / نسب:</span>
                                            <div>{{ Str::limit($agent->contract_terms, 55) }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 11.5px;">— لا توجد بنود —</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size: 12px;">
                                        @if($agent->user?->phone)
                                            <div><span dir="ltr">{{ $agent->user->phone }}</span></div>
                                        @endif
                                        @if($agent->user?->email)
                                            <div class="text-muted" dir="ltr" style="font-size: 11px;">{{ $agent->user->email }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $propCount > 0 ? 'gold' : 'secondary' }}">
                                        {{ $propCount }} عقار
                                    </span>
                                </td>
                                <td>
                                    <form method="post" action="{{ admin_route('admin.agents.verify', $agent) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="ap-btn ap-btn--sm {{ $agent->isVerified() ? 'ap-btn--success' : 'ap-btn--ghost' }}" title="{{ $agent->isVerified() ? 'إلغاء الاعتماد' : 'توثيق واعتماد' }}" style="padding: 2px 8px; font-size: 11px;">
                                            @if($agent->isVerified())
                                                ✓ معتمد
                                            @else
                                                توثيق
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" action="{{ admin_route('admin.agents.status', $agent) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="ap-btn ap-btn--sm {{ $agent->status === 'active' ? 'ap-btn--ghost' : 'ap-btn--danger' }}" style="padding: 2px 8px; font-size: 11px;">
                                            <span class="ap-badge ap-badge--{{ $agent->status === 'active' ? 'success' : 'danger' }}" style="padding:0; background:transparent;">
                                                {{ $agent->status === 'active' ? 'نشط' : 'معطل' }}
                                            </span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="{{ admin_route('admin.agents.edit', $agent) }}" class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 14])
                                        </a>
                                        <a href="{{ route('agents.show', ['locale' => 'ar', 'slug' => $agent->slug]) }}" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm" title="عرض في الموقع">
                                            @include('admin.partials.icon', ['iconName' => 'external', 'size' => 14])
                                        </a>
                                        @can('users.delete')
                                            <form method="post" action="{{ admin_route('admin.agents.destroy', $agent) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الوكيل نهائياً؟');" style="display:inline;">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm" title="حذف" style="padding: 4px 6px;">
                                                    @include('admin.partials.icon', ['iconName' => 'trash', 'size' => 14])
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    لا يوجد وكلاء يطابقون خيارات البحث.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($agents->hasPages())
                <div class="p-3 border-top">
                    {{ $agents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

