@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    <div class="ap-alert ap-alert--info">
        @include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18])
        <div>
            <strong>هيكل الصلاحيات:</strong>
            سوبر أدمن (كل شيء) <span class="ap-badge">100</span> ←
            أدمن (كل شيء) <span class="ap-badge">90</span> ←
            مسؤول (صلاحيات محدودة) <span class="ap-badge">60</span> ←
            سمسار (أقل صلاحيات) <span class="ap-badge">30</span>.
            المنع/السماح يُطبَّق فوراً على أول جلسة تالية للمستخدم.
        </div>
    </div>

    {{-- ---------------------------------------------------- مصفوفة الصلاحيات --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <div>
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18]) مصفوفة الصلاحيات</h2>
                <p class="ap-card__subtitle">اضغط على مربع لتشغيل/إيقاف الصلاحية ثم اضغط "حفظ" أسفل العمود</p>
            </div>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th style="min-width: 240px;">الصلاحية</th>
                            @foreach ($roles as $role)
                                <th class="text-center" style="min-width: 110px;">
                                    <div>{{ $role->label_ar ?: $role->name }}</div>
                                    <div class="ap-badge ap-badge--{{ $role->level >= 90 ? 'gold' : ($role->level >= 55 ? 'info' : 'secondary') }}"
                                        style="margin-top: 4px;">مستوى {{ $role->level }}</div>
                                    <div class="ap-help">{{ $role->users_count }} مستخدم</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissionGroups as $group => $permissions)
                            <tr>
                                <th colspan="{{ $roles->count() + 1 }}"
                                    style="background: var(--ap-primary-100); text-align: right; padding: 8px 14px;">
                                    {{ $groupLabels[$group] ?? $group }}
                                </th>
                            </tr>
                            @foreach ($permissions as $permission)
                                <tr>
                                    <td>
                                        <div class="fw-bold" style="font-size: 13px;">{{ $permission->label_ar ?: $permission->name }}</div>
                                        <code style="font-size: 11px; color: var(--ap-muted);">{{ $permission->slug }}</code>
                                    </td>
                                    @foreach ($roles as $role)
                                        @php $has = $role->permissions->contains('id', $permission->id); @endphp
                                        <td class="text-center">
                                            <input type="checkbox" style="width: 18px; height: 18px; accent-color: var(--ap-primary);"
                                                name="permissions[{{ $role->id }}][]" value="{{ $permission->id }}"
                                                form="roleForm{{ $role->id }}" @checked($has)>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ap-card__body" style="border-top: 1px solid var(--ap-border);">
            <div class="d-flex flex-wrap gap-3">
                @foreach ($roles as $role)
                    <form id="roleForm{{ $role->id }}" method="post"
                        action="{{ admin_route('admin.roles.permissions', $role) }}"
                        class="d-flex align-items-center gap-2"
                        data-confirm="حفظ صلاحيات {{ $role->label_ar }}؟">
                        @csrf
                        <span class="ap-badge ap-badge--secondary">{{ $role->label_ar }}</span>
                        <button class="ap-btn ap-btn--sm ap-btn--ghost">حفظ العمود</button>
                        <a href="{{ admin_route('admin.roles.show', $role) }}" class="ap-btn ap-btn--sm ap-btn--ghost">تفاصيل</a>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--2" style="align-items: start;">
        {{-- ------------------------------------------------------- إضافة دور --}}
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 18]) إضافة دور جديد</h2>
            </div>
            <form method="post" action="{{ admin_route('admin.roles.store') }}">
                @csrf
                <div class="ap-card__body">
                    <div class="ap-grid ap-grid--2">
                        <div class="ap-field">
                            <label class="ap-label">الاسم بالإنجليزية <span>*</span></label>
                            <input type="text" name="name" class="ap-input" required value="{{ old('name') }}" placeholder="Regional Manager">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">الاسم بالعربية <span>*</span></label>
                            <input type="text" name="label_ar" class="ap-input" required value="{{ old('label_ar') }}" placeholder="مدير إقليمي">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">المُعرّف (slug)</label>
                            <input type="text" name="slug" class="ap-input" value="{{ old('slug') }}" placeholder="regional-manager">
                            <div class="ap-help">اتركه فارغاً ليُشتق من الاسم الإنجليزي.</div>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">المستوى</label>
                            <input type="number" name="level" class="ap-input" min="1" max="99" value="{{ old('level', 20) }}">
                            <div class="ap-help">الأعلى = صلاحيات أوسع (الأدمن 90، المسؤول 60، السمسار 30).</div>
                        </div>
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الوصف</label>
                        <textarea name="description" class="ap-textarea" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الصلاحيات المبدئية</label>
                        <div class="ap-grid ap-grid--3">
                            @foreach ($permissionGroups->flatten() as $permission)
                                <label class="ap-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}">
                                    {{ $permission->label_ar ?: $permission->slug }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="ap-card__body" style="border-top: 1px solid var(--ap-border); padding-top: 12px; padding-bottom: 12px;">
                    <button class="ap-btn ap-btn--gold ap-btn--sm">إنشاء الدور</button>
                </div>
            </form>
        </div>

        {{-- --------------------------------------------------- تعديل الأدوار --}}
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'edit', 'size' => 18]) تعديل الأدوار</h2>
            </div>
            <div class="ap-card__body">
                @foreach ($roles as $role)
                    <form method="post" action="{{ admin_route('admin.roles.update', $role) }}"
                        class="ap-grid ap-grid--4" style="align-items: end; border-bottom: 1px solid var(--ap-border); padding-bottom: 12px; margin-bottom: 12px;">
                        @csrf
                        @method('put')
                        <div class="ap-field" style="margin-bottom: 0;">
                            <label class="ap-label">{{ $role->slug }}</label>
                            <input type="text" name="name" class="ap-input ap-input--sm" value="{{ $role->name }}" required @disabled($role->slug === 'super-admin')>
                        </div>
                        <div class="ap-field" style="margin-bottom: 0;">
                            <label class="ap-label">بالعربية</label>
                            <input type="text" name="label_ar" class="ap-input ap-input--sm" value="{{ $role->label_ar }}" required @disabled($role->slug === 'super-admin')>
                        </div>
                        <div class="ap-field" style="margin-bottom: 0;">
                            <label class="ap-label">المستوى</label>
                            <input type="number" name="level" class="ap-input ap-input--sm" value="{{ $role->level }}" min="1" max="100" @disabled($role->slug === 'super-admin')>
                        </div>
                        <div class="d-flex gap-1">
                            @if ($role->slug !== 'super-admin')
                                <button class="ap-btn ap-btn--sm">حفظ</button>
                            @endif
                            @if (! $role->is_system)
                                <button type="submit" form="deleteRole{{ $role->id }}" class="ap-btn ap-btn--sm ap-btn--danger">حذف</button>
                            @endif
                        </div>
                        <div class="ap-field" style="grid-column: 1 / -1; margin-bottom: 0;">
                            <input type="text" name="description" class="ap-input ap-input--sm" value="{{ $role->description }}" placeholder="وصف الدور" @disabled($role->slug === 'super-admin')>
                        </div>
                    </form>
                    @if (! $role->is_system)
                        <form id="deleteRole{{ $role->id }}" method="post" action="{{ admin_route('admin.roles.destroy', $role) }}"
                            data-confirm="حذف الدور {{ $role->label_ar }} نهائياً؟">
                            @csrf
                            @method('delete')
                        </form>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endsection
