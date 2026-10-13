<?php

namespace App\Services\Preferences;

use App\Models\Currency;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

class VisitorPreferences
{
    public const COOKIE_MINUTES = 525600;

    public function locale(Request $request): string
    {
        $supported = config('locales.supported', ['ar']);
        $candidates = [
            $request->user()?->preferred_locale,
            $request->cookie('homzen_locale'),
            $request->session()->get('locale'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                return $candidate;
            }
        }

        return config('locales.default', 'ar');
    }

    public function rememberLocale(Request $request, string $locale): void
    {
        $request->session()->put('locale', $locale);

        if ($request->user() && $request->user()->preferred_locale !== $locale) {
            $request->user()->forceFill(['preferred_locale' => $locale])->saveQuietly();
        }
    }

    public function localeCookie(Request $request, string $locale): Cookie
    {
        return cookie(
            'homzen_locale',
            $locale,
            self::COOKIE_MINUTES,
            '/',
            null,
            $request->isSecure(),
            false,
            false,
            'lax'
        );
    }

    public function currency(Request $request): string
    {
        $candidates = [
            $request->user()?->preferred_currency,
            $request->cookie('homzen_currency'),
            $request->session()->get('currency'),
            env('DEFAULT_CURRENCY', 'EGP'),
        ];

        foreach ($candidates as $candidate) {
            $code = strtoupper((string) $candidate);
            if ($code !== '' && $this->currencyIsActive($code)) {
                return $code;
            }
        }

        return 'EGP';
    }

    public function currencyIsActive(string $code): bool
    {
        try {
            return Currency::where('code', strtoupper($code))->where('is_active', true)->exists();
        } catch (Throwable) {
            return in_array(strtoupper($code), ['EGP', 'USD'], true);
        }
    }
}
