@extends('layouts.admin')

@section('title', 'طلبات التقديم للوظائف')
@section('page_title', 'طلبات التقديم للوظائف')
@section('page_subtitle', 'قائمة السير الذاتية واستمارات المتقدمين لفرص العمل')

@section('page_actions')
    <a href="{{ admin_route('admin.careers.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 16]) قائمة الوظائف
    </a>
@endsection

@section('content')
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 18])
                الطلبات المستلمة
                <span class="ap-badge">{{ number_format($applications->total()) }}</span>
            </h2>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>المتقدم</th>
                            <th>بيانات الاتصال</th>
                            <th>تفاصيل ومؤهلات المتقدم</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $app)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $app->name }}</div>
                                    <span class="ap-badge ap-badge--info" style="font-size: 11px;">وظائف دارلي</span>
                                </td>
                                <td>
                                    <div><a href="tel:{{ $app->phone }}" dir="ltr">{{ $app->phone }}</a></div>
                                    <div class="text-muted" style="font-size: 11px;" dir="ltr">{{ $app->email }}</div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; max-width: 480px; white-space: pre-wrap;">{{ $app->notes ?: $app->message }}</div>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size: 12px;">{{ $app->created_at?->diffForHumans() }}</span>
                                </td>
                                <td>
                                    <a href="{{ admin_route('admin.leads.show', $app) }}" class="ap-btn ap-btn--ghost ap-btn--sm" title="تفاصيل">
                                        @include('admin.partials.icon', ['iconName' => 'eye', 'size' => 14])
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    لا توجد طلبات تقديم حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($applications->hasPages())
                <div class="p-3 border-top">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

