@php
    $isCompany = auth()->user()?->isCompany();
    $current = request()->route()?->getName() ?? '';
    $nav = [
        ['dashboard.index', 'نظرة عامة', 'dashboard.index'],
        ['dashboard.properties.index', 'عقاراتي', 'dashboard.properties.*'],
    ];
    if ($isCompany) $nav[] = ['dashboard.projects.index', 'مشاريع الشركة', 'dashboard.projects.*'];
    $nav[] = ['dashboard.customer-requests.index', 'طلبات العملاء', 'dashboard.customer-requests.index'];
    $nav[] = ['dashboard.customer-requests.stats', 'إحصائيات الطلبات', 'dashboard.customer-requests.stats'];
    $nav[] = ['dashboard.customer-requests.create', 'إضافة طلب', 'dashboard.customer-requests.create'];
@endphp
<aside class="cr-card cr-sidebar">
    <div class="cr-side-title">لوحة التحكم</div>
    <nav class="cr-nav">
        @foreach ($nav as [$route, $label, $pattern])
            @php $active = str_ends_with($pattern, '*') ? str_starts_with($current, rtrim($pattern, '*')) : $current === $pattern; @endphp
            <a href="{{ route($route) }}" class="{{ $active ? 'active' : '' }}">
                <span>{{ $label }}</span>
                @if ($route === 'dashboard.customer-requests.index')
                    <span class="cr-count">{{ number_format(array_sum($counts ?? [])) }}</span>
                @endif
            </a>
        @endforeach
    </nav>
    <div class="cr-side-title">حالات الطلبات</div>
    <div class="cr-side-stats">
        @foreach ($statuses ?? [] as $key => $label)
            <a href="{{ route('dashboard.customer-requests.index', ['status' => $key]) }}" class="cr-side-stat">
                <span>{{ $label }}</span><strong>{{ number_format($counts[$key] ?? 0) }}</strong>
            </a>
        @endforeach
    </div>
    @if (! empty($locationCounts))
        <div class="cr-side-title">أكثر المواقع طلبًا</div>
        <div class="cr-side-stats">
            @foreach ($locationCounts as $row)
                <a href="{{ route('dashboard.customer-requests.index', ['location_id' => $row['id']]) }}" class="cr-side-stat">
                    <span>{{ \Illuminate\Support\Str::limit($row['label'], 24) }}</span><strong>{{ number_format($row['total']) }}</strong>
                </a>
            @endforeach
        </div>
    @endif
</aside>
