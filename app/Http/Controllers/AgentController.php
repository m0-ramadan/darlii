<?php

namespace App\Http\Controllers;

use App\Models\AgentProfile;
use App\Services\Admin\PageViewTracker;
use App\Services\LegacyFrontend\LegacyFrontendRenderer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentController
{
    /** Marker that starts the agent profile block in the mirrored pages. */
    private const DETAIL_MARKER = '<section class="agent-detail-section">';

    public function __construct(private readonly LegacyFrontendRenderer $renderer) {}

    public function index(Request $request)
    {
        $locale = app()->getLocale();
        PageViewTracker::track('agents');

        $query = AgentProfile::query()
            ->with(['user.properties', 'agency', 'translations'])
            ->where('status', 'active');

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->whereHas('translations', fn ($t) => $t->where('display_name', 'like', "%{$term}%"))
                    ->orWhereHas('agency', fn ($a) => $a->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"))
                    ->orWhere('job_title', 'like', "%{$term}%")
                    ->orWhere('license_number', 'like', "%{$term}%")
                    ->orWhere('bio', 'like', "%{$term}%");
            });
        }

        if ($request->filled('location')) {
            $loc = trim((string) $request->input('location'));
            $query->where(function ($q) use ($loc) {
                $q->whereHas('translations', fn ($t) => $t->where('bio', 'like', "%{$loc}%")->orWhere('display_name', 'like', "%{$loc}%"))
                    ->orWhereHas('agency', fn ($a) => $a->where('description', 'like', "%{$loc}%")->orWhere('name', 'like', "%{$loc}%"))
                    ->orWhere('bio', 'like', "%{$loc}%");
            });
        }

        $agents = $query->orderByRaw('CASE WHEN verified_at IS NOT NULL THEN 0 ELSE 1 END')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('agents.index', [
            'agents' => $agents,
            'shell' => $this->shell($locale),
        ]);
    }

    public function show(Request $request, ?string $param1 = null, ?string $param2 = null): Response
    {
        $slug = $param2 ?? $param1 ?? '';
        $locale = $param2 ? $param1 : (app()->getLocale() ?: config('locales.default', 'ar'));

        $agent = AgentProfile::with(['user', 'agency', 'translations'])
            ->where('slug', $slug)
            ->first();

        /*
         * صفحة السمسار تُعرض من قاعدة البيانات (فريق دارلي) مع إعادة استخدام
         * هيدر وفوتر تصميم هومزن. ولو التصميم غير متاح نرجع للعرض البسيط.
         */
        if ($agent) {
            PageViewTracker::track('agents', [$slug]);

            return response(view('agents.show', [
                'agent' => $agent,
                'properties' => $agent->user?->properties()->with(['translations', 'media', 'currency'])->latest()->limit(9)->get() ?? collect(),
                'shell' => $this->shell($locale, $slug),
            ]));
        }

        // ملفات السماسرة القديمة من نسخة الميرور تبقى متاحة كما هي.
        $legacyHtml = $this->renderer->render($request, $locale . '/agents/' . $slug);

        abort_if($legacyHtml === null, 404);

        return response($legacyHtml)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /** @return array{head:string, footer:string}|null */
    private function shell(string $locale, string $slug = 'amanda'): ?array
    {
        $shell = $this->renderer->shell($locale, 'agents/' . $slug, self::DETAIL_MARKER);

        if ($shell !== null) {
            return $shell;
        }

        $root = storage_path('app/legacy-site/' . $locale . '/agents');

        if (! is_dir($root)) {
            $root = storage_path('app/legacy-site/en/agents');
        }

        foreach ((array) glob($root . '/*') as $file) {
            if (is_file($file)) {
                return $this->renderer->shell($locale, 'agents/' . basename($file), self::DETAIL_MARKER);
            }
        }

        return $this->renderer->shell($locale, 'projects', LegacyFrontendRenderer::listingsMarker());
    }
}
