<?php

namespace App\Http\Controllers;

use App\Services\Localization\LocalizedUrlResolver;
use App\Services\Preferences\VisitorPreferences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(
        Request $request,
        string $locale,
        VisitorPreferences $preferences,
        LocalizedUrlResolver $localizedUrlResolver
    ): RedirectResponse {
        abort_unless(in_array($locale, config('locales.supported', []), true), 404);

        app()->setLocale($locale);
        $preferences->rememberLocale($request, $locale);

        $destination = $this->destination($request, $locale, $localizedUrlResolver);

        return redirect($destination)->withCookie($preferences->localeCookie($request, $locale));
    }

    private function destination(Request $request, string $locale, LocalizedUrlResolver $resolver): string
    {
        $fallback = '/';
        $redirect = $request->query('redirect');

        if (! is_string($redirect) || $redirect === '') {
            return $fallback;
        }

        $parts = parse_url(html_entity_decode($redirect));
        if ($parts === false) {
            return $fallback;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host !== '' && $host !== strtolower($request->getHost())) {
            return $fallback;
        }

        $path = '/' . ltrim((string) ($parts['path'] ?? '/'), '/');
        $cleanPath = trim($path, '/');
        $cleanPath = preg_replace('#^(?:ar|en|vi|fr|id)(?:/|$)#i', '', $cleanPath) ?? $cleanPath;

        if ($cleanPath === '') {
            $path = $fallback;
        } else {
            $path = '/' . ltrim($cleanPath, '/');
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';

        return $path . $query . $fragment;
    }
}
