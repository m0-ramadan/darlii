@php
    $tabs = [
        ['admin.finance.index', 'نظرة عامة', 'admin.finance.index'],
        ['admin.finance.stats', 'الإحصائيات', 'admin.finance.stats*'],
        ['admin.finance.point-types.index', 'أنواع النقاط', 'admin.finance.point-types.*'],
        ['admin.finance.point-rules.index', 'النقاط الدورية', 'admin.finance.point-rules.*'],
        ['admin.finance.point-transactions.index', 'حركات النقاط', 'admin.finance.point-transactions.*'],
        ['admin.finance.deposits.index', 'الودائع', 'admin.finance.deposits.*'],
        ['admin.finance.expenses.index', 'المصروفات', 'admin.finance.expenses.*'],
        ['admin.finance.balances.index', 'أرصدة المستخدمين', 'admin.finance.balances.*'],
    ];
    if (! config('finance.points_enabled', false)) {
        $tabs = array_values(array_filter($tabs, fn ($tab) => ! str_contains($tab[0], 'point-')));
    }
    $current = request()->route()?->getName() ?? '';
@endphp
<div class="ap-card" style="margin-bottom: 18px;">
    <div class="ap-card__body" style="display:flex;gap:8px;flex-wrap:wrap;padding:12px 16px;">
        @foreach ($tabs as [$route, $label, $pattern])
            @php $active = str_ends_with($pattern, '*') ? str_starts_with($current, rtrim($pattern, '*')) : $current === $pattern; @endphp
            <a href="{{ admin_route($route) }}"
               class="ap-btn ap-btn--sm {{ $active ? 'ap-btn--primary' : 'ap-btn--ghost' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>
