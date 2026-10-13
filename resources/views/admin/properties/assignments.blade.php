@extends('layouts.admin')

@section('title', 'إسناد العقارات')
@section('page_title', 'إسناد العقارات')
@section('page_subtitle', 'العقارات التي لا يوجد لها مسؤول أو سمسار — يجب إسناد كل عقار لمستخدم مسؤول')

@section('content')
    <div class="ap-alert ap-alert--info">
        @include('admin.partials.icon', ['iconName' => 'alert', 'size' => 18])
        <div>
            عدد العقارات غير المُسندة: <strong>{{ $properties->total() }}</strong>.
            الإسناد يجعل العقار يظهر في لوحة المسؤول أو السمسار المختص ويتيح له إدارته.
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) بحاجة إلى إسناد</h2>
            <form method="get" class="d-flex gap-2">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm" placeholder="بحث بالكود أو الاسم">
                <button class="ap-btn ap-btn--sm ap-btn--ghost">بحث</button>
            </form>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>العقار</th>
                            <th>السعر</th>
                            <th>الحالة</th>
                            <th>الموقع</th>
                            <th>إسناد إلى</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($properties as $property)
                            <tr>
                                <td><code>{{ $property->reference_code }}</code></td>
                                <td>
                                    <a href="{{ admin_route('admin.properties.show', $property) }}" class="fw-bold">
                                        {{ \Illuminate\Support\Str::limit($property->displayName('ar'), 40) }}
                                    </a>
                                </td>
                                <td>{{ money_eg($property->price) }}</td>
                                <td><span class="ap-badge ap-badge--{{ status_color($property->status) }}">{{ status_label($property->status) }}</span></td>
                                <td>{{ $property->location?->translation('ar')?->name ?? '—' }}</td>
                                <td>
                                    <form method="post" action="{{ admin_route('admin.properties.assign', $property) }}" class="d-flex gap-1">
                                        @csrf
                                        <select name="owner_id" class="ap-select ap-select--sm" required>
                                            @foreach ($assignableUsers as $assignee)
                                                <option value="{{ $assignee->id }}">{{ $assignee->name }} — {{ user_role_label($assignee->primary_role_slug) }}</option>
                                            @endforeach
                                        </select>
                                        <button class="ap-btn ap-btn--sm">إسناد</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 42])
                                        <p>كل العقارات مُسندة بالفعل. 🎉</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($properties->hasPages())
            <div class="ap-pagination">{{ $properties->links() }}</div>
        @endif
    </div>
@endsection
