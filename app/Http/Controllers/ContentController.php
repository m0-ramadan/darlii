<?php

namespace App\Http\Controllers;

use App\Models\Career;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Post;
use App\Services\Admin\PageViewTracker;
use App\Services\LegacyFrontend\LegacyFrontendRenderer;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(private readonly LegacyFrontendRenderer $renderer) {}

    public function blog()
    {
        PageViewTracker::track('blog');

        return view('blog.index', [
            'posts' => Post::with(['translations', 'author'])->where('status', 'published')->latest('published_at')->paginate(12),
            'shell' => $this->shell('blog'),
        ]);
    }

    public function post(?string $param1 = null, ?string $param2 = null)
    {
        $slug = $param2 ?? $param1 ?? '';
        $locale = $param2 ? $param1 : (app()->getLocale() ?: config('locales.default', 'ar'));

        $post = Post::with('translations')
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->firstOrFail();

        PageViewTracker::track('blog', [$slug]);

        return view('blog.show', [
            'post' => $post,
            'related' => Post::with('translations')->where('id', '!=', $post->id)->where('status', 'published')->latest('published_at')->limit(3)->get(),
            'shell' => $this->shell('blog'),
        ]);
    }

    public function careers()
    {
        $locale = app()->getLocale();
        PageViewTracker::track('careers');

        $query = Career::with('translations')
            ->where('status', 'published')
            ->latest('id');

        if (request()->filled('q')) {
            $term = trim((string) request()->input('q'));
            $query->where(function ($q) use ($term) {
                $q->whereHas('translations', fn ($t) => $t->where('title', 'like', "%{$term}%")->orWhere('summary', 'like', "%{$term}%"))
                    ->orWhere('department', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%");
            });
        }

        if (request()->filled('department')) {
            $query->where('department', request('department'));
        }

        if (request()->filled('type')) {
            $query->where('employment_type', request('type'));
        }

        $departments = Career::query()->where('status', 'published')->whereNotNull('department')->distinct()->pluck('department')->filter();

        return view('careers.index', [
            'careers' => $query->paginate(12)->withQueryString(),
            'departments' => $departments,
            'shell' => $this->shell('careers'),
        ]);
    }

    public function career(?string $param1 = null, ?string $param2 = null)
    {
        $slug = $param2 ?? $param1 ?? '';
        $locale = $param2 ? $param1 : (app()->getLocale() ?: config('locales.default', 'ar'));

        $career = Career::with('translations')
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->firstOrFail();

        PageViewTracker::track('careers', [$slug]);

        return view('careers.show', [
            'career' => $career,
            'shell' => $this->shell('careers'),
        ]);
    }

    public function faqs()
    {
        $locale = app()->getLocale();
        PageViewTracker::track('faqs');

        $page = Page::with('translations')->where('key', 'faqs')->first();
        $translation = $page?->translation($locale);

        $rawContent = $translation?->content ?? '';
        $faqsList = [];
        if (!empty($rawContent)) {
            $decoded = json_decode($rawContent, true);
            if (is_array($decoded)) {
                $faqsList = $decoded;
            }
        }

        return view('faqs.index', [
            'page' => $page,
            'faqs' => $faqsList,
            'shell' => $this->shell('faqs'),
        ]);
    }

    public function privacyPolicy()
    {
        $locale = app()->getLocale();
        PageViewTracker::track('privacy-policy');

        $page = Page::with('translations')->where('key', 'privacy-policy')->first();

        return view('pages.privacy-policy', [
            'page' => $page,
            'shell' => $this->shell('privacy-policy'),
        ]);
    }

    public function terms()
    {
        $locale = app()->getLocale();
        PageViewTracker::track('terms-and-conditions');

        $page = Page::with('translations')->where('key', 'terms-and-conditions')->first();

        return view('pages.terms-and-conditions', [
            'page' => $page,
            'shell' => $this->shell('terms-and-conditions'),
        ]);
    }

    public function contactUs()
    {
        $locale = app()->getLocale();
        PageViewTracker::track('contact-us');

        $page = Page::with('translations')->where('key', 'contact-us')->first();

        $contactInfo = [
            'phone' => (string) setting('contact.phone', '+20 10 34905812'),
            'whatsapp' => (string) setting('contact.whatsapp', '+20 10 34905812'),
            'email' => (string) setting('contact.email', 'contact@darlii.eg'),
            'address' => (string) setting($locale === 'ar' ? 'contact.address_ar' : 'contact.address_en', 'شارع صلاح سالم (تقاطع كورنيش النيل)، مدينة بني سويف'),
            'facebook' => (string) setting('contact.facebook', 'https://facebook.com'),
            'instagram' => (string) setting('contact.instagram', 'https://instagram.com'),
        ];

        return view('contact.index', [
            'page' => $page,
            'contactInfo' => $contactInfo,
            'shell' => $this->shell('contact-us'),
        ]);
    }

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $lead = Lead::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'message' => (!empty($data['subject']) ? "[الموضوع: {$data['subject']}]\n" : '') . $data['message'],
            'source' => 'contact-page',
            'status' => 'new',
            'reference_code' => 'CNT-' . strtoupper(substr(uniqid(), -6)),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => app()->getLocale() === 'ar'
                    ? 'تم إرسال رسالتك بنجاح! سيتواصل معك فريق دارلي في أقرب وقت.'
                    : 'Your message has been sent successfully! Our team will contact you shortly.',
                'reference_code' => $lead->reference_code,
            ]);
        }

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم إرسال رسالتك بنجاح! رقم المتابعة الخاص بك: ' . $lead->reference_code . '. سيتواصل معك فريق دارلي في أقرب وقت.'
            : 'Your message has been sent successfully! Reference: ' . $lead->reference_code . '. Our team will contact you shortly.'
        );
    }

    /** @return array{head:string, footer:string}|null */
    private function shell(string $page): ?array
    {
        $shell = $this->renderer->shell(
            app()->getLocale(),
            $page,
            LegacyFrontendRenderer::listingsMarker()
        );

        if ($shell !== null) {
            return $shell;
        }

        return $this->renderer->shell(
            app()->getLocale(),
            'projects',
            LegacyFrontendRenderer::listingsMarker()
        );
    }
}
