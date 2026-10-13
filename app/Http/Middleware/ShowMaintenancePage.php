<?php

namespace App\Http\Middleware;

use App\Services\Settings\SettingsRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowMaintenancePage
{
    public function __construct(private SettingsRepository $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->settings->get('general.maintenance_mode', false) || $this->isDashboardRequest($request)) {
            return $next($request);
        }

        return response()
            ->view('maintenance', [
                'title' => $this->settings->get('maintenance.title', 'الموقع تحت الصيانة'),
                'message' => $this->settings->get('maintenance.message', 'نعمل حاليًا على تحسين تجربتك. سنعود قريبًا.'),
                'logo' => $this->settings->get('branding.logo_ar'),
            ], Response::HTTP_SERVICE_UNAVAILABLE)
            ->header('Retry-After', '3600');
    }

    private function isDashboardRequest(Request $request): bool
    {
        return $request->is('admin', 'admin/*', 'ar/dashboard', 'ar/dashboard/*', 'en/dashboard', 'en/dashboard/*');
    }
}
