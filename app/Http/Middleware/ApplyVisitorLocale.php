<?php

namespace App\Http\Middleware;

use App\Services\Preferences\VisitorPreferences;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyVisitorLocale
{
    public function __construct(private readonly VisitorPreferences $preferences) {}

    public function handle(Request $request, Closure $next): Response
    {
        $routeLocale = (string) $request->route('locale', '');
        $locale = in_array($routeLocale, config('locales.supported', []), true)
            ? $routeLocale
            : $this->preferences->locale($request);

        app()->setLocale($locale);
        view()->share('currentLocale', $locale);

        return $next($request);
    }
}
