@if ($paginator->hasPages())
    @php $locale = app()->getLocale(); @endphp
    <nav role="navigation" aria-label="{{ $locale === 'ar' ? 'التنقل بين الصفحات' : 'Pagination Navigation' }}"
         class="ap-pagination-bar">
        {{-- أزرار التنقل --}}
        <ul class="pagination">
            {{-- السابق --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">{{ $locale === 'ar' ? '‹' : '›' }}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                       aria-label="{{ $locale === 'ar' ? 'السابق' : 'Previous' }}">{{ $locale === 'ar' ? '‹' : '›' }}</a>
                </li>
            @endif

            {{-- أرقام الصفحات --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- التالي --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"
                       aria-label="{{ $locale === 'ar' ? 'التالي' : 'Next' }}">{{ $locale === 'ar' ? '›' : '‹' }}</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">{{ $locale === 'ar' ? '›' : '‹' }}</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
