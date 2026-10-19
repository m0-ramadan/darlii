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
        // الروابط العامة أصبحت بدون بادئة لغة، لذا نعتمد أولًا على {locale}
        // إن وُجد في الراوت، وإلا على تفضيلات الزائر (cookie/session/حسابه).
        $routeLocale = (string) $request->route('locale', '');
        $locale = in_array($routeLocale, config('locales.supported', []), true)
            ? $routeLocale
            : $this->preferences->locale($request);

        // إذا كانت الإنجليزية معطّلة من الإعدادات نرجع للعربية بدون توجيه،
        // حتى لا يعلق الزائر في حلقة إعادة توجيه.
        if ($locale === 'en' && ! app()->runningUnitTests() && ! (bool) setting('general.language_en_enabled', false)) {
            $locale = (string) config('locales.default', 'ar');
        }

        abort_unless(in_array($locale, config('locales.supported', []), true), 404);

        app()->setLocale($locale);
        $this->preferences->rememberLocale($request, $locale);
        view()->share('currentLocale', $locale);

        return $next($request)->withCookie($this->preferences->localeCookie($request, $locale));
    }
}
