@extends('layouts.app')

@section('title', __('messages.blog'))

@push('head')
    <style>
        .darli-pagination-wrap {
            align-items: center;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 32px;
        }

        .darli-pagination {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin: 0;
            padding: 0;
        }

        .darli-page-item {
            list-style: none;
        }

        .darli-page-link {
            align-items: center;
            background: #fff;
            border: 1px solid #dfe3ea;
            border-radius: 8px;
            color: #161e2d;
            display: inline-flex;
            font-size: 14px;
            font-weight: 600;
            justify-content: center;
            min-height: 40px;
            min-width: 42px;
            padding: 8px 14px;
            text-decoration: none;
            transition: .2s;
        }

        .darli-page-link:hover {
            border-color: #1563df;
            color: #1563df;
        }

        .darli-page-item.active .darli-page-link {
            background: #1563df;
            border-color: #1563df;
            color: #fff;
        }

        .darli-page-item.disabled .darli-page-link {
            background: #f6f7f9;
            color: #98a1b2;
            cursor: not-allowed;
            pointer-events: none;
        }

        .darli-pagination-summary {
            color: #6b7280;
            font-size: 14px;
            text-align: center;
        }

        html[data-theme="dark"] .darli-page-link {
            background: #171c26;
            border-color: #2f3747;
            color: #e5e7eb;
        }

        html[data-theme="dark"] .darli-page-link:hover {
            background: #1f2633;
            border-color: #5b7cff;
            color: #ffffff;
        }

        html[data-theme="dark"] .darli-page-item.active .darli-page-link {
            background: #4f46e5;
            border-color: #6d64ff;
            color: #ffffff;
        }

        html[data-theme="dark"] .darli-page-item.disabled .darli-page-link {
            background: #11151d;
            border-color: #252c3a;
            color: #6b7280;
        }

        html[data-theme="dark"] .darli-pagination-summary {
            color: #9ca3af;
        }
    </style>
@endpush

@section('content')
    <div class="container page-section">
        <h1 class="mb-4">{{ __('messages.blog') }}</h1>

        <div class="row g-4">
            @foreach ($posts as $p)
                @php($tr = $p->translation())
                <div class="col-md-6 col-lg-4">
                    <article class="border rounded-4 p-4 h-100">
                        <h4>
                            <a href="{{ route('blog.show', ['locale' => app()->getLocale(), 'slug' => $tr?->slug]) }}">
                                {{ $tr?->title }}
                            </a>
                        </h4>
                        <p class="text-muted">{{ $tr?->excerpt }}</p>
                    </article>
                </div>
            @endforeach
        </div>

        @if ($posts->hasPages())
            <nav class="darli-pagination-wrap" aria-label="{{ __('messages.blog') }}">
                <ul class="darli-pagination">
                    <li class="darli-page-item @if ($posts->onFirstPage()) disabled @endif">
                        @if ($posts->onFirstPage())
                            <span class="darli-page-link">{{ app()->getLocale() === 'ar' ? 'السابق' : 'Previous' }}</span>
                        @else
                            <a class="darli-page-link" href="{{ $posts->previousPageUrl() }}" rel="prev">
                                {{ app()->getLocale() === 'ar' ? 'السابق' : 'Previous' }}
                            </a>
                        @endif
                    </li>

                    @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                        <li class="darli-page-item @if ($page === $posts->currentPage()) active @endif">
                            @if ($page === $posts->currentPage())
                                <span class="darli-page-link">{{ $page }}</span>
                            @else
                                <a class="darli-page-link" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach

                    <li class="darli-page-item @unless ($posts->hasMorePages()) disabled @endunless">
                        @if ($posts->hasMorePages())
                            <a class="darli-page-link" href="{{ $posts->nextPageUrl() }}" rel="next">
                                {{ app()->getLocale() === 'ar' ? 'التالي' : 'Next' }}
                            </a>
                        @else
                            <span class="darli-page-link">{{ app()->getLocale() === 'ar' ? 'التالي' : 'Next' }}</span>
                        @endif
                    </li>
                </ul>

                <div class="darli-pagination-summary">
                    {{ app()->getLocale() === 'ar'
                        ? 'عرض ' . $posts->firstItem() . ' إلى ' . $posts->lastItem() . ' من ' . $posts->total() . ' نتيجة'
                        : 'Showing ' . $posts->firstItem() . ' to ' . $posts->lastItem() . ' of ' . $posts->total() . ' results' }}
                </div>
            </nav>
        @endif
    </div>
@endsection
