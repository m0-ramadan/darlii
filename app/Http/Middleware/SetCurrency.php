<?php

namespace App\Http\Middleware;

use App\Services\Preferences\VisitorPreferences;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrency
{
    public function __construct(private readonly VisitorPreferences $preferences)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $requested = strtoupper((string) $request->query('currency', ''));
        $currency = $requested !== '' && $this->preferences->currencyIsActive($requested)
            ? $requested
            : $this->preferences->currency($request);

        $request->session()->put('currency', $currency);
        view()->share('currentCurrency', $currency);

        if ($request->user() && $request->user()->preferred_currency !== $currency) {
            $request->user()->forceFill(['preferred_currency' => $currency])->saveQuietly();
        }

        return $next($request)->withCookie(cookie(
            'homzen_currency',
            $currency,
            VisitorPreferences::COOKIE_MINUTES,
            '/',
            null,
            $request->isSecure(),
            false,
            false,
            'lax'
        ));
    }
}
