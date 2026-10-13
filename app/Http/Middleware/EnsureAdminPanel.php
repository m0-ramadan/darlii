<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the whole /admin panel:
 * - guests are sent to the localized login page
 * - only active users holding a panel role may continue
 * - the panel locale follows the user's own preference (Arabic by default)
 */
class EnsureAdminPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('admin.login'));
        }

        if ($user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->guest(route('admin.login'))
                ->withErrors(['email' => 'حسابك موقوف، تواصل مع مدير النظام.']);
        }

        if (! $user->hasAnyRole(\App\Support\PermissionCatalog::panelRoles())) {
            abort(403, 'ليست لديك صلاحية الوصول إلى لوحة التحكم.');
        }

        $locale = $user->preferred_locale ?: config('locales.default', 'ar');
        if (in_array($locale, config('locales.supported', ['ar']), true)) {
            app()->setLocale($locale);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();

        return $next($request);
    }
}
