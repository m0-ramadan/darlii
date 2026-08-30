<?php

namespace App\Http\Controllers;

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

    public function home(Request $request, string $locale): Response
    {
        $localizedHome = $locale . '/homepage-4';
        $html = $this->renderer->render($request, $localizedHome);

        if ($html === null && $locale === 'en') {
            $html = $this->renderer->render($request, 'homepage-4');
        }

        abort_if($html === null, 404);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function page(Request $request, string $locale, string $path = ''): Response
    {
        $resolved = trim($locale . '/' . $path, '/');
        $html = $this->renderer->render($request, $resolved);

        if ($html === null) {
            $legacyPath = $this->localizedUrlResolver->legacyMirrorPath($locale, $path);
            if ($legacyPath !== $path) {
                $html = $this->renderer->render($request, trim($locale . '/' . $legacyPath, '/'));
            }
        }

        if ($html === null && $locale === 'en') {
            // The root archive contains the English versions of a few pages.
            $html = $this->renderer->render($request, trim($path, '/'));
        }

        abort_if($html === null, 404);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function rootPage(Request $request, string $path = ''): Response
    {
        if (! preg_match('/\.(?:xml|txt|json|rss|atom)$/i', $path)) {
            $locale = $this->visitorPreferences->locale($request);

            return redirect('/' . $this->localizedUrlResolver->forLocale($path, $locale), 301);
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
