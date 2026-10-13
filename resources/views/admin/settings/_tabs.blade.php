@php
    $user = auth()->user();
@endphp

<div class="ap-tabs">
    @foreach ($tabs as $key => $tab)
        @continue($tab['permission'] && $user && ! $user->hasPermission($tab['permission']))
        <a href="{{ admin_route($tab['route']) }}" class="ap-tab @if ($active === $key) is-active @endif">
            @include('admin.partials.icon', ['iconName' => $tab['icon'], 'size' => 16])
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
