<?php

namespace App\Http\Middleware;

use App\Services\Preferences\VisitorPreferences;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(private readonly VisitorPreferences $preferences) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale', config('locales.default'));
        abort_unless(in_array($locale, config('locales.supported'), true), 404);

        app()->setLocale($locale);
        $this->preferences->rememberLocale($request, $locale);
        view()->share('currentLocale', $locale);

        return $next($request)->withCookie($this->preferences->localeCookie($request, $locale));
    }
}
