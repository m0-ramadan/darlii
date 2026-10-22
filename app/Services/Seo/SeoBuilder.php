<?php

namespace App\Services\Seo;

use App\Models\AgentProfile;
use App\Models\Career;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use Illuminate\Support\Str;

/**
 * يبني وسوم SEO ديناميكية من بيانات الصفحة الفعلية (عقار/مشروع/مقال/وكيل…)
 * ويستبدل أي وسوم ثابتة قادمة من نسخة هومزن (جوهانسبرغ، صور ثابتة…).
 */
class SeoBuilder
{
    private ?array $meta = null;

    /** @return array{title:string,description:string,canonical:string,image:string,type:string,locale:string,site_name:string,robots:string,alternates:array<string,string>,json_ld:?array} */
    public function forCurrentRequest(): array
    {
        if ($this->meta !== null) {
            return $this->meta;
        }

        $locale = app()->getLocale() ?: (string) config('locales.default', 'ar');
        $canonical = $this->canonicalUrl();

        $meta = [
            'title' => (string) setting("seo.title_{$locale}", setting('general.site_name', config('app.name', 'دارلي'))),
            'description' => (string) setting("seo.description_{$locale}", ''),
            'canonical' => $canonical,
            'image' => $this->absolute($this->defaultImage($locale)),
            'type' => 'website',
            'locale' => $locale === 'ar' ? 'ar_EG' : 'en_US',
            'site_name' => (string) setting('general.site_name', config('app.name', 'دارلي')),
            'robots' => 'index,follow',
            'alternates' => ['x-default' => $canonical],
            'json_ld' => null,
        ];

        try {
            $meta = $this->buildForRoute($meta, $locale);
        } catch (\Throwable $e) {
            // لا نكسر الصفحة بسبب SEO.
        }

        if ($this->shouldNoIndex()) {
            $meta['robots'] = 'noindex,nofollow';
        }

        return $this->meta = $meta;
    }

    private function buildForRoute(array $meta, string $locale): array
    {
        $route = request()->route();
        if (! $route) {
            return $meta;
        }

        $name = (string) $route->getName();

        return match (true) {
            $name === 'home' => $this->home($meta, $locale),
            $name === 'properties.index' => $this->propertiesIndex($meta, $locale),
            $name === 'properties.show' => $this->propertyShow($meta, $locale, (string) $route->parameter('slug')),
            $name === 'projects.index' => $this->projectsIndex($meta, $locale),
            $name === 'projects.show' => $this->projectShow($meta, $locale, (string) $route->parameter('slug')),
            $name === 'agents.index' => $this->agentsIndex($meta, $locale),
            $name === 'agents.show' => $this->agentShow($meta, $locale, (string) $route->parameter('slug')),
            $name === 'blog.index' => $this->blogIndex($meta, $locale),
            $name === 'blog.show' => $this->postShow($meta, $locale, (string) $route->parameter('slug')),
            $name === 'careers.index' => $this->staticPage($meta, $locale, 'careers', url('/careers'), $this->t($locale, 'الوظائف', 'Careers')),
            $name === 'careers.show' => $this->careerShow($meta, $locale, (string) $route->parameter('slug')),
            $name === 'faqs.index' => $this->staticPage($meta, $locale, 'faqs', url('/faqs'), $this->t($locale, 'الأسئلة الشائعة', 'Frequently Asked Questions')),
            $name === 'about-us.index' => $this->staticPage($meta, $locale, 'about-us', url('/about-us'), $this->t($locale, 'من نحن', 'About us')),
            $name === 'contact-us.index' => $this->staticPage($meta, $locale, 'contact-us', url('/contact-us'), $this->t($locale, 'تواصل معنا', 'Contact us')),
            $name === 'privacy-policy.index' => $this->staticPage($meta, $locale, 'privacy-policy', url('/privacy-policy'), $this->t($locale, 'سياسة الخصوصية', 'Privacy policy')),
            $name === 'terms.index' => $this->staticPage($meta, $locale, 'terms-and-conditions', url('/terms-and-conditions'), $this->t($locale, 'الشروط والأحكام', 'Terms and conditions')),
            default => $meta,
        };
    }

    private function home(array $meta, string $locale): array
    {
        $meta['title'] = (string) setting("seo.title_{$locale}", $meta['site_name']);
        $meta['description'] = (string) setting("seo.description_{$locale}", $meta['description']);
        $meta['canonical'] = url('/');
        $alternates = [];
        foreach ((array) config('locales.supported', ['ar']) as $code) {
            $alternates[$code] = $meta['canonical'];
        }
        $alternates['x-default'] = $meta['canonical'];
        $meta['alternates'] = $alternates;

        return $meta;
    }

    private function propertiesIndex(array $meta, string $locale): array
    {
        $total = Property::query()->whereIn('status', ['published', 'sold'])->count();

        $meta['title'] = $this->t(
            $locale,
            'عقارات للبيع والإيجار في بني سويف | ' . $meta['site_name'],
            'Properties for Sale and Rent in Beni Suef | ' . $meta['site_name']
        );
        $meta['description'] = $this->t(
            $locale,
            "تصفح {$total} عقارًا للبيع والإيجار في بني سويف ومصر: شقق، فيلات، أراضي ومشروعات مع الصور والأسعار والتفاصيل.",
            "Browse {$total} properties for sale and rent in Beni Suef and Egypt: apartments, villas, land and projects with photos, prices and details."
        );
        $meta['canonical'] = $this->listingCanonical(url('/properties'));
        $meta['alternates'] = ['x-default' => $meta['canonical']];

        return $meta;
    }

    private function propertyShow(array $meta, string $locale, string $slug): array
    {
        $property = Property::with([
            'translations', 'currency', 'media', 'location.translations',
            'category.translations', 'project.translations',
        ])->whereHas('translations', fn ($q) => $q->where('slug', $slug))->first();

        if (! $property) {
            return $meta;
        }

        $tr = $property->translation($locale);
        $name = $tr?->name ?: ($property->reference_code ?: ('#' . $property->id));
        $location = $property->location?->translation($locale)?->name;
        $purpose = $property->purpose === 'rent'
            ? $this->t($locale, 'للإيجار', 'for rent')
            : $this->t($locale, 'للبيع', 'for sale');
        $category = $property->category?->translation($locale)?->name;

        $titleParts = array_filter([
            $name,
            $purpose,
            $location ? $this->t($locale, 'في ' . $location, 'in ' . $location) : null,
        ]);
        $meta['title'] = $this->pickTitle([
            implode(' | ', $titleParts),
            trim($name . ' | ' . $purpose),
            $name,
        ], $meta['site_name']);

        $description = $tr?->seo_description
            ?: $tr?->short_description
            ?: Str::limit(trim(strip_tags((string) $tr?->description)), 158);

        if (blank($description)) {
            $bits = array_filter([
                $name,
                $category,
                $property->area ? $this->num($property->area) . ' ' . $this->t($locale, 'م²', 'm²') : null,
                $property->bedrooms ? $property->bedrooms . ' ' . $this->t($locale, 'غرف', 'beds') : null,
                $property->bathrooms ? $property->bathrooms . ' ' . $this->t($locale, 'حمام', 'baths') : null,
                $location,
                $this->priceText($property, $locale),
            ]);
            $description = implode(' — ', $bits);
        }

        $meta['description'] = $this->description($description);
        $meta['type'] = 'article';
        $meta['canonical'] = url('/properties/' . ($tr?->slug ?: $slug));
        $meta['image'] = $this->absolute($property->primaryImageUrl($locale));
        $meta['alternates'] = $this->propertyAlternates($property, $meta['canonical']);

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateListing',
            'name' => $name,
            'description' => $meta['description'],
            'url' => $meta['canonical'],
            'image' => [$meta['image']],
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'addressLocality' => $location,
                'streetAddress' => $tr?->address_text,
                'addressCountry' => 'EG',
            ]),
            'offers' => [
                '@type' => 'Offer',
                'price' => (float) $property->price,
                'priceCurrency' => $property->currency?->code ?: 'EGP',
                'availability' => $property->isSold()
                    ? 'https://schema.org/SoldOut'
                    : 'https://schema.org/InStock',
            ],
        ];

        if ($property->latitude !== null && $property->longitude !== null) {
            $jsonLd['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $property->latitude,
                'longitude' => (float) $property->longitude,
            ];
        }
        if ($property->bedrooms) {
            $jsonLd['numberOfRooms'] = (int) $property->bedrooms;
        }
        if ($property->area) {
            $jsonLd['floorSize'] = [
                '@type' => 'QuantitativeValue',
                'value' => (float) $property->area,
                'unitCode' => 'MTK',
            ];
        }
        if ($property->published_at) {
            $jsonLd['datePosted'] = $property->published_at->toIso8601String();
        }

        $meta['json_ld'] = $jsonLd;

        return $meta;
    }

    private function projectsIndex(array $meta, string $locale): array
    {
        $total = Project::query()->where('status', 'published')->count();

        $meta['title'] = $this->t(
            $locale,
            'المشروعات العقارية في بني سويف | ' . $meta['site_name'],
            'Real estate projects in Beni Suef | ' . $meta['site_name']
        );
        $meta['description'] = $this->t(
            $locale,
            "اكتشف {$total} مشروعًا عقاريًا في بني سويف ومصر مع الوحدات والأسعار والمخططات ومواقع المشروعات.",
            "Discover {$total} real estate projects in Beni Suef and Egypt with units, prices, plans and locations."
        );
        $meta['canonical'] = $this->listingCanonical(url('/projects'));
        $meta['alternates'] = ['x-default' => $meta['canonical']];

        return $meta;
    }

    private function projectShow(array $meta, string $locale, string $slug): array
    {
        $project = Project::with(['translations', 'location.translations', 'properties.media', 'properties.translations'])
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->first();

        if (! $project) {
            return $meta;
        }

        $tr = $project->translation($locale);
        $name = $tr?->name ?: ('#' . $project->id);
        $location = $project->location?->translation($locale)?->name;

        $meta['title'] = $this->pickTitle([
            trim($name . ($location ? ' | ' . $location : '')),
            $name,
        ], $meta['site_name']);
        $description = $tr?->seo_description
            ?: $tr?->short_description
            ?: Str::limit(trim(strip_tags((string) $tr?->description)), 158);
        $meta['description'] = $this->description($description ?: $this->t($locale, "تفاصيل مشروع {$name} والوحدات المتاحة والأسعار.", "Details of {$name}, available units and prices."));

        $firstProperty = $project->properties->first();
        $meta['image'] = $this->absolute(
            $firstProperty?->primaryImageUrl($locale) ?: $this->defaultImage($locale)
        );
        $meta['canonical'] = url('/projects/' . ($tr?->slug ?: $slug));
        $meta['alternates'] = $this->projectAlternates($project, $meta['canonical']);
        $meta['json_ld'] = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Residence',
            'name' => $name,
            'description' => $meta['description'],
            'url' => $meta['canonical'],
            'image' => [$meta['image']],
            'address' => $location ? [
                '@type' => 'PostalAddress',
                'addressLocality' => $location,
                'addressCountry' => 'EG',
            ] : null,
        ]);

        return $meta;
    }

    private function agentsIndex(array $meta, string $locale): array
    {
        $meta['title'] = $this->t(
            $locale,
            'تعرف على نخبة وكلائنا العقاريين | ' . $meta['site_name'],
            'Meet Our Elite Real Estate Agents | ' . $meta['site_name']
        );
        $meta['description'] = $this->t(
            $locale,
            'تعرف على نخبة الوكلاء والشركات العقارية المعتمدة في بني سويف، مع بيانات التواصل والعقارات المتاحة.',
            'Meet the elite verified real estate agents and agencies in Beni Suef with contact details and listings.'
        );
        $meta['canonical'] = $this->listingCanonical(url('/agents'));
        $meta['alternates'] = ['x-default' => $meta['canonical']];

        return $meta;
    }

    private function agentShow(array $meta, string $locale, string $slug): array
    {
        $agent = AgentProfile::with(['translations', 'agency', 'user'])->where('slug', $slug)->first();
        if (! $agent) {
            return $meta;
        }

        $tr = $agent->translation($locale);
        $name = $tr?->display_name ?: ($agent->user?->name ?: $slug);
        $agency = $agent->agency?->name;

        $meta['title'] = $this->headline(trim($name . ($agency ? ' | ' . $agency : '')), $meta['site_name']);
        $meta['description'] = $this->description(
            $tr?->seo_description
                ?: $tr?->bio
                ?: $this->t($locale, "ملف {$name} والعقارات المتاحة للبيع والإيجار.", "{$name} profile and available properties for sale and rent.")
        );
        $meta['image'] = $this->absolute(
            $this->mediaUrl($agent->avatar_path) ?: $this->defaultImage($locale)
        );
        $meta['canonical'] = url('/agents/' . ($agent->slug ?: $slug));
        $meta['alternates'] = $this->agentAlternates($agent, $meta['canonical']);
        $meta['json_ld'] = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => $name,
            'description' => $meta['description'],
            'url' => $meta['canonical'],
            'image' => $meta['image'],
            'worksFor' => $agency ? ['@type' => 'Organization', 'name' => $agency] : null,
            'telephone' => $agent->whatsapp,
            'address' => $agent->job_title ? null : null,
        ]);

        return $meta;
    }

    private function blogIndex(array $meta, string $locale): array
    {
        $meta['title'] = $this->t($locale, 'المدونة العقارية | ' . $meta['site_name'], 'Real estate blog | ' . $meta['site_name']);
        $meta['description'] = $this->t(
            $locale,
            'مقالات ونصائح عن السوق العقاري في مصر: الشراء والبيع والإيجار والاستثمار العقاري.',
            'Articles and tips about the Egyptian real estate market: buying, selling, renting and investing.'
        );
        $meta['canonical'] = $this->listingCanonical(url('/blog'));
        $meta['alternates'] = ['x-default' => $meta['canonical']];

        return $meta;
    }

    private function postShow(array $meta, string $locale, string $slug): array
    {
        $post = Post::with(['translations', 'author'])
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->first();
        if (! $post) {
            return $meta;
        }

        $tr = $post->translation($locale);
        $title = $tr?->title ?: $slug;

        $meta['title'] = $this->headline($title, $meta['site_name']);
        $meta['description'] = $this->description(
            $tr?->seo_description
                ?: $tr?->excerpt
                ?: strip_tags((string) $tr?->content)
        );
        $meta['image'] = $this->absolute($this->mediaUrl($post->featured_image) ?: $this->defaultImage($locale));
        $meta['type'] = 'article';
        $meta['canonical'] = url('/blog/' . ($tr?->slug ?: $slug));
        $meta['alternates'] = $this->postAlternates($post, $meta['canonical']);
        $meta['json_ld'] = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $title,
            'description' => $meta['description'],
            'url' => $meta['canonical'],
            'mainEntityOfPage' => $meta['canonical'],
            'image' => [$meta['image']],
            'datePublished' => optional($post->published_at)->toIso8601String(),
            'dateModified' => optional($post->updated_at)->toIso8601String(),
            'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : null,
            'publisher' => ['@type' => 'Organization', 'name' => $meta['site_name']],
        ]);

        return $meta;
    }

    private function careerShow(array $meta, string $locale, string $slug): array
    {
        $career = Career::with('translations')
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->first();
        if (! $career) {
            return $meta;
        }

        $tr = $career->translation($locale);
        $title = $tr?->title ?: $slug;

        $meta['title'] = $this->headline($title, $meta['site_name']);
        $meta['description'] = $this->description(
            $tr?->seo_description ?: $tr?->summary ?: strip_tags((string) $tr?->description)
        );
        $meta['canonical'] = url('/careers/' . ($tr?->slug ?: $slug));
        $meta['alternates'] = ['x-default' => $meta['canonical']];
        $meta['json_ld'] = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $title,
            'description' => $meta['description'],
            'url' => $meta['canonical'],
            'datePosted' => optional($career->created_at)->toIso8601String(),
            'validThrough' => optional($career->deadline)->toIso8601String(),
            'employmentType' => $career->employment_type,
            'hiringOrganization' => ['@type' => 'Organization', 'name' => $meta['site_name']],
            'jobLocation' => $career->location ? [
                '@type' => 'Place',
                'address' => ['@type' => 'PostalAddress', 'addressLocality' => $career->location, 'addressCountry' => 'EG'],
            ] : null,
        ]);

        return $meta;
    }

    private function staticPage(array $meta, string $locale, string $key, string $url, string $fallbackTitle): array
    {
        $page = Page::with('translations')->where('key', $key)->first();
        $tr = $page?->translation($locale);

        $title = $tr?->seo_title ?: $tr?->title ?: $fallbackTitle;
        $meta['title'] = $this->headline($title, $meta['site_name']);
        $meta['description'] = $this->description(
            $tr?->seo_description ?: strip_tags((string) $tr?->content) ?: $fallbackTitle
        );
        $meta['canonical'] = $url;
        $meta['alternates'] = ['x-default' => $url];

        return $meta;
    }

    /** يبني وسوم الـhead النهائية ويستبدل أي وسوم قديمة. */
    public function applyToHead(string $head, array $meta): string
    {
        if ($head === '') {
            return $head;
        }

        $locale = $meta['locale'] ?? app()->getLocale();
        $lang = str_starts_with((string) $locale, 'ar') ? 'ar' : 'en';
        $dir = $lang === 'ar' ? 'rtl' : 'ltr';

        $head = preg_replace('/<html\b[^>]*>/i', '<html lang="' . $lang . '" dir="' . $dir . '">', $head, 1) ?? $head;

        // إزالة الوسوم الثابتة القديمة (هومزن/جوهانسبرغ) قبل إضافة الجديدة.
        // ملاحظة: بعض وسوم الميرور تستخدم قيمًا بدون علامات اقتباس، لذا نقرأ
        // الخصائص يدويًا بدل الاعتماد على regex صارم.
        $head = preg_replace('/<title\b[^>]*>.*?<\/title>/is', '', $head) ?? $head;

        $head = preg_replace_callback('#<meta\b[^>]*>#i', function (array $match): string {
            $key = strtolower($this->attribute($match[0], '(?:name|property)'));
            if ($key === 'description' || $key === 'keywords' || $key === 'robots' || $key === 'author'
                || str_starts_with($key, 'og:') || str_starts_with($key, 'twitter:')) {
                return '';
            }

            return $match[0];
        }, $head) ?? $head;

        $head = preg_replace_callback('#<link\b[^>]*>#i', function (array $match): string {
            $rel = strtolower($this->attribute($match[0], 'rel'));

            return in_array($rel, ['canonical', 'alternate'], true) ? '' : $match[0];
        }, $head) ?? $head;

        $head = preg_replace_callback('#<script\b[^>]*>.*?</script>#is', function (array $match): string {
            $type = strtolower($this->attribute($match[0], 'type'));

            return $type === 'application/ld+json' ? '' : $match[0];
        }, $head) ?? $head;

        $tags = $this->tags($meta);

        if (stripos($head, '</head>') !== false) {
            return preg_replace('/<\/head>/i', $tags . "\n</head>", $head, 1) ?? $head;
        }

        return $head . $tags;
    }

    private function tags(array $meta): string
    {
        $e = static fn (?string $value): string => e((string) $value);
        $title = $e($meta['title'] ?? '');
        $description = $e($meta['description'] ?? '');
        $canonical = $e($meta['canonical'] ?? '');
        $image = $e($meta['image'] ?? '');
        $type = $e($meta['type'] ?? 'website');
        $locale = $e((string) ($meta['locale'] ?? 'ar'));
        $siteName = $e($meta['site_name'] ?? '');
        $robots = $e($meta['robots'] ?? 'index,follow');

        $html = <<<HTML
<title>{$title}</title>
<meta name="description" content="{$description}">
<meta name="robots" content="{$robots}">
<link rel="canonical" href="{$canonical}">
<meta property="og:site_name" content="{$siteName}">
<meta property="og:type" content="{$type}">
<meta property="og:title" content="{$title}">
<meta property="og:description" content="{$description}">
<meta property="og:url" content="{$canonical}">
<meta property="og:image" content="{$image}">
<meta property="og:image:alt" content="{$title}">
<meta property="og:locale" content="{$locale}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{$title}">
<meta name="twitter:description" content="{$description}">
<meta name="twitter:image" content="{$image}">
HTML;

        foreach ((array) ($meta['alternates'] ?? []) as $code => $url) {
            $html .= "\n" . '<link rel="alternate" hreflang="' . $e((string) $code) . '" href="' . $e((string) $url) . '">';
        }

        if (! empty($meta['json_ld'])) {
            $json = json_encode($meta['json_ld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json !== false) {
                $html .= "\n" . '<script type="application/ld+json">' . $json . '</script>';
            }
        }

        return $html;
    }

    private function propertyAlternates(Property $property, string $canonical): array
    {
        $alternates = [];
        foreach ((array) config('locales.supported', ['ar']) as $code) {
            $translation = $property->translations->firstWhere('locale', $code);
            $alternates[$code] = $translation?->slug ? url('/properties/' . $translation->slug) : $canonical;
        }
        $alternates['x-default'] = $canonical;

        return $alternates;
    }

    private function projectAlternates(Project $project, string $canonical): array
    {
        $alternates = [];
        foreach ((array) config('locales.supported', ['ar']) as $code) {
            $translation = $project->translations->firstWhere('locale', $code);
            $alternates[$code] = $translation?->slug ? url('/projects/' . $translation->slug) : $canonical;
        }
        $alternates['x-default'] = $canonical;

        return $alternates;
    }

    private function postAlternates(Post $post, string $canonical): array
    {
        $alternates = [];
        foreach ((array) config('locales.supported', ['ar']) as $code) {
            $translation = $post->translations->firstWhere('locale', $code);
            $alternates[$code] = $translation?->slug ? url('/blog/' . $translation->slug) : $canonical;
        }
        $alternates['x-default'] = $canonical;

        return $alternates;
    }

    private function agentAlternates(AgentProfile $agent, string $canonical): array
    {
        $alternates = [];
        foreach ((array) config('locales.supported', ['ar']) as $code) {
            $translation = $agent->translations->firstWhere('locale', $code);
            $alternates[$code] = $translation ? $canonical : $canonical;
        }
        $alternates['x-default'] = $canonical;

        return $alternates;
    }

    private function canonicalUrl(): string
    {
        $base = url()->current();
        $page = (int) request()->query('page', 0);

        return $page > 1 ? $base . '?page=' . $page : $base;
    }

    private function listingCanonical(string $base): string
    {
        $page = (int) request()->query('page', 0);

        return $page > 1 ? $base . '?page=' . $page : $base;
    }

    private function shouldNoIndex(): bool
    {
        $path = trim(request()->path(), '/');

        return (bool) preg_match('#^(admin|dashboard|login|register|password|email|api|favorites|wishlist|language|currency)(/|$)#', $path);
    }

    private function defaultImage(string $locale): string
    {
        $configured = (string) setting($locale === 'ar' ? 'branding.logo_ar' : 'branding.logo_en', '');
        if ($configured !== '') {
            return $this->absolute($configured);
        }

        $hero = 'themes/homzen/images/darli-hero-cairo-photo-clean.webp';
        if (is_file(public_path($hero))) {
            return asset($hero);
        }

        $file = $locale === 'ar'
            ? 'themes/homzen/images/logo/darli-logo-ar.png'
            : 'themes/homzen/images/logo/darli-logo-en.png';

        return asset($file);
    }

    private function mediaUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        if (str_starts_with($path, 'themes/') || str_starts_with($path, 'legacy/') || str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private function absolute(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return asset('themes/homzen/images/logo/darli-logo-ar.png');
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url('/' . ltrim($url, '/'));
    }

    private function priceText(Property $property, string $locale): string
    {
        if (! $property->price || (float) $property->price <= 0) {
            return $this->t($locale, 'السعر عند التواصل', 'Price on request');
        }

        $symbol = $property->currency?->symbol ?: $this->t($locale, 'ج.م', 'EGP');
        $price = number_format((float) $property->price, 0);

        return $this->t($locale, 'السعر', 'Price') . ': ' . $price . ' ' . $symbol
            . ($property->purpose === 'rent' ? $this->t($locale, ' / شهريًا', ' / month') : '');
    }

    private function containsBrand(string $text, string $site): bool
    {
        foreach (array_filter([$site, 'دارلي', 'Darlii']) as $needle) {
            if ($needle !== '' && mb_stripos($text, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /** يختار أول عنوان يناسب الطول، ويضيف اسم الموقع إن أمكن. */
    private function pickTitle(array $candidates, string $site, int $max = 70): string
    {
        $clean = array_values(array_filter(array_map(
            static fn (string $value): string => trim((string) preg_replace('/\s+/u', ' ', $value)),
            $candidates
        )));

        foreach ($clean as $core) {
            if ($this->containsBrand($core, $site)) {
                return $core;
            }
        }

        foreach ($clean as $core) {
            $withSite = $core . ' | ' . $site;
            if (mb_strlen($withSite) <= $max) {
                return $withSite;
            }
        }

        foreach ($clean as $core) {
            if (mb_strlen($core) <= $max) {
                return $core;
            }
        }

        $fallback = $clean[0] ?? $site;

        return rtrim(mb_substr($fallback, 0, $max - 1), " \t\n\r\0\x0B.,،") . '…';
    }

    private function headline(string $core, string $site, int $max = 70): string
    {
        $core = trim((string) preg_replace('/\s+/u', ' ', $core));

        if ($this->containsBrand($core, $site)) {
            return $core;
        }

        $suffix = ' | ' . $site;
        $title = mb_strlen($core . $suffix) <= $max ? $core . $suffix : $core;

        if (mb_strlen($title) > $max) {
            $title = rtrim(mb_substr($title, 0, $max - 1), " \t\n\r\0\x0B.,،") . '…';
        }

        return $title;
    }

    private function description(?string $text, int $limit = 158): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

        if ($text === '' || mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > (int) ($limit * 0.6)) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " \t\n\r\0\x0B.,،") . '…';
    }

    private function num(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    }

    /** قراءة قيمة خاصية HTML مع أو بدون علامات اقتباس. */
    private function attribute(string $tag, string $names): string
    {
        if (preg_match('#\b' . $names . '\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))#i', $tag, $matches)) {
            foreach ([1, 2, 3] as $index) {
                if (isset($matches[$index]) && $matches[$index] !== '') {
                    return trim((string) $matches[$index]);
                }
            }
        }

        return '';
    }

    private function t(string $locale, string $ar, string $en): string
    {
        return $locale === 'ar' ? $ar : $en;
    }
}
