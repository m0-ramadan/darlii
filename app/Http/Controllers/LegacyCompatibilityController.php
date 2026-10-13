<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Currency;
use App\Models\Lead;
use App\Models\NewsletterSubscriber;
use App\Models\Project;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Preferences\VisitorPreferences;
use App\Services\LegacyFrontend\HomepageBlockRenderer;
use Illuminate\Validation\Rule;

class LegacyCompatibilityController extends Controller
{
    public function newsletter(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:190']]);
        $locale = $this->localeFromRequest($request);

        NewsletterSubscriber::updateOrCreate(
            ['email' => strtolower($data['email'])],
            ['locale' => $locale, 'status' => 'active']
        );

        return $this->success($request, 'Subscribed successfully.');
    }

    public function consult(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:190'],
            'content' => ['required', 'string', 'max:10000'],
            'type' => ['required', 'in:property,project'],
            'data_id' => ['required', 'integer'],
            'consult_custom_fields.1' => ['nullable', 'date'],
        ]);

        $leadData = [
            'reference_code' => 'LEAD-' . now()->format('Y') . '-' . strtoupper(Str::random(8)),
            'user_id' => $request->user()?->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'message' => $data['content'],
            'source' => 'consultation',
            'status' => 'new',
            'ip_hash' => hash('sha256', (string) $request->ip() . config('app.key')),
        ];

        if ($data['type'] === 'property') {
            $leadData['property_id'] = Property::find($data['data_id'])?->id;
        } else {
            $leadData['project_id'] = Project::find($data['data_id'])?->id;
        }

        $lead = Lead::create($leadData);
        $lead->activities()->create(['type' => 'created', 'description' => 'Lead created from original Homzen consultation form']);

        if ($date = data_get($data, 'consult_custom_fields.1')) {
            Appointment::create([
                'lead_id' => $lead->id,
                'property_id' => $lead->property_id,
                'user_id' => $request->user()?->id,
                'scheduled_at' => $date,
                'timezone' => config('app.timezone', 'UTC'),
                'status' => 'requested',
            ]);
        }

        return $this->success($request, 'Message sent successfully.');
    }

    public function contact(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:250'],
            'address' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:10000'],
            'message' => ['nullable', 'string', 'max:10000'],
        ]);

        $message = $data['content'] ?? $data['message'] ?? '';
        if (! empty($data['subject'])) {
            $message = "Subject: {$data['subject']}\n" . $message;
        }
        if (! empty($data['address'])) {
            $message .= "\nAddress: {$data['address']}";
        }

        $lead = Lead::create([
            'reference_code' => 'LEAD-' . now()->format('Y') . '-' . strtoupper(Str::random(8)),
            'user_id' => $request->user()?->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $message,
            'source' => 'contact',
            'status' => 'new',
            'ip_hash' => hash('sha256', (string) $request->ip() . config('app.key')),
        ]);
        $lead->activities()->create(['type' => 'created', 'description' => 'Lead created from original Homzen contact form']);

        return $this->success($request, 'Message sent successfully.');
    }

    public function review(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        abort_unless($request->user(), 401);
        $data = $request->validate([
            'star' => ['required', 'integer', 'between:1,5'],
            'content' => ['required', 'string', 'max:10000'],
            'reviewable_type' => ['nullable', 'string'],
        ]);

        $isProject = Str::contains((string) ($data['reviewable_type'] ?? ''), 'Project');
        if ($isProject) {
            $item = Project::whereHas('translations', fn ($q) => $q->where('slug', $slug))->firstOrFail();
            $type = Project::class;
        } else {
            $item = Property::whereHas('translations', fn ($q) => $q->where('slug', $slug))->firstOrFail();
            $type = Property::class;
        }

        Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'reviewable_type' => $type, 'reviewable_id' => $item->id],
            ['rating' => $data['star'], 'comment' => $data['content'], 'status' => 'pending']
        );

        return $this->success($request, 'Review submitted for moderation.');
    }

    public function currency(Request $request, string $code): RedirectResponse
    {
        $currency = Currency::where('code', strtoupper($code))->where('is_active', true)->first();
        if ($currency) {
            $request->session()->put('currency', $currency->code);
            if ($request->user()) {
                $request->user()->forceFill(['preferred_currency' => $currency->code])->saveQuietly();
            }
        }

        $response = redirect()->back();

        return $currency
            ? $response->withCookie(cookie('homzen_currency', $currency->code, VisitorPreferences::COOKIE_MINUTES, '/', null, $request->isSecure(), false, false, 'lax'))
            : $response;
    }

    public function renderUiBlocks(Request $request, HomepageBlockRenderer $renderer): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', Rule::in(HomepageBlockRenderer::BLOCKS)],
            'shortcodeId' => ['nullable', 'string', 'max:120'],
            'attributes' => ['nullable', 'array'],
        ]);
        $locale = $this->localeFromRequest($request);
        $html = $renderer->render(
            $data['name'],
            $data['attributes'] ?? [],
            $locale,
            $data['shortcodeId'] ?? null
        );

        return response()->json([
            'error' => false,
            'data' => $html,
        ]);
    }

    public function projectSearch(Request $request): JsonResponse
    {
        $locale = $this->localeFromRequest($request);
        $keyword = trim((string) $request->query('k', $request->query('q', '')));
        $projects = Project::query()
            ->with('translations')
            ->where('status', 'published')
            ->when($keyword !== '', function ($query) use ($keyword, $locale): void {
                $query->whereHas('translations', fn ($translation) => $translation
                    ->where('locale', $locale)
                    ->where('name', 'like', '%' . addcslashes($keyword, '%_') . '%'));
            })
            ->limit(10)
            ->get();

        $items = $projects->map(function (Project $project) use ($locale): string {
            $translation = $project->translation($locale);

            return '<li data-value="' . $project->id . '" class="search-suggestion-item">'
                . e($translation?->name ?? $project->reference_code)
                . '</li>';
        })->implode('');

        return response()->json([
            'error' => false,
            'data' => '<ul class="search-suggestion">' . $items . '</ul>',
            'message' => null,
        ]);
    }

    public function feed(Request $request, ?string $type = 'properties'): \Symfony\Component\HttpFoundation\Response
    {
        $type = $type ?: 'properties';
        $file = storage_path('app/legacy-site/feed/' . $type);

        if (is_file($file)) {
            return response(file_get_contents($file))->header('Content-Type', 'application/xml; charset=UTF-8');
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Darlii Feed</title><link>' . url('/') . '</link><description>Darlii Feed</description></channel></rss>';
        return response($xml)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function localeFromRequest(Request $request): string
    {
        $supported = config('locales.supported', ['ar', 'en']);
        $first = explode('/', trim($request->path(), '/'))[0] ?? 'ar';
        if (in_array($first, $supported, true)) {
            return $first;
        }

        foreach ([$request->query('locale'), $request->cookie('homzen_locale')] as $candidate) {
            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                return $candidate;
            }
        }

        $refererPath = parse_url((string) $request->headers->get('referer'), PHP_URL_PATH);
        $refererLocale = explode('/', trim((string) $refererPath, '/'))[0] ?? null;

        return in_array($refererLocale, $supported, true) ? $refererLocale : config('locales.default', 'ar');
    }

    private function success(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['error' => false, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
