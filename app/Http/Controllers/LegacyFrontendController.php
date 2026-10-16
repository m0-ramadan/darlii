<?php

namespace App\Http\Controllers;

use App\Services\Admin\PageViewTracker;
use App\Services\LegacyFrontend\LegacyFrontendRenderer;
use App\Services\Localization\LocalizedUrlResolver;
use App\Services\Preferences\VisitorPreferences;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyFrontendController extends Controller
{
    public function __construct(
        private readonly LegacyFrontendRenderer $renderer,
        private readonly LocalizedUrlResolver $localizedUrlResolver,
        private readonly VisitorPreferences $visitorPreferences
    ) {}

    public function home(Request $request, ?string $locale = null): Response
    {
        $locale = $locale ?: (app()->getLocale() ?: config('locales.default', 'ar'));
        $localizedHome = $locale . '/homepage-4';
        $html = $this->renderer->render($request, $localizedHome);

        if ($html === null) {
            $html = $this->renderer->render($request, 'ar/homepage-4')
                ?? $this->renderer->render($request, 'homepage-4');
        }

        abort_if($html === null, 404);

        PageViewTracker::track('home');

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function page(Request $request, ?string $param1 = null, string $path = ''): Response
    {
        $supported = config('locales.supported', ['ar', 'en']);
        if ($param1 !== null && ! in_array($param1, $supported, true)) {
            $path = $path !== '' ? $param1 . '/' . $path : $param1;
            $locale = app()->getLocale() ?: config('locales.default', 'ar');
        } else {
            $locale = $param1 ?: (app()->getLocale() ?: config('locales.default', 'ar'));
        }

        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        $resolved = trim($locale . '/' . $path, '/');
        $html = $this->renderer->render($request, $resolved);

        if ($html === null && $locale !== 'ar') {
            $html = $this->renderer->render($request, trim('ar/' . $path, '/'));
        }

        if ($html === null) {
            $html = $this->renderer->render($request, trim($path, '/'));
        }

        if ($html === null) {
            $legacyPath = $this->localizedUrlResolver->legacyMirrorPath($locale, $path);
            if ($legacyPath !== $path) {
                $html = $this->renderer->render($request, trim($locale . '/' . $legacyPath, '/'))
                    ?? $this->renderer->render($request, trim('ar/' . $legacyPath, '/'));
            }
        }

        abort_if($html === null, 404);

        PageViewTracker::track($segments[0] ?? 'home', array_slice($segments, 1));

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function rootPage(Request $request, string $path = ''): Response
    {
        if (! preg_match('/\.(?:xml|txt|json|rss|atom)$/i', $path)) {
            $locale = $this->visitorPreferences->locale($request);

            // Public URLs are intentionally locale-free. Rendering here avoids
            // redirecting /about-us (and similar legacy pages) back to itself.
            return $this->page($request, $locale, $path);
        }

        return $this->html($request, trim($path, '/'));
    }

    private function html(Request $request, string $path): Response
    {
        $html = $this->renderer->render($request, $path);
        abort_if($html === null, 404);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
