<?php

namespace App\Services\Seo;

use App\Models\AgentProfile;
use App\Models\Career;
use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use Illuminate\Support\Facades\Cache;

/**
 * يبني ملف sitemap.xml من بيانات الموقع الفعلية ويحدّثه تلقائيًا.
 * يعتمد على الروابط النظيفة بدون بادئة لغة.
 */
class SitemapGenerator
{
    public const LAST_GENERATED_KEY = 'sitemap:last_generated';

    public function write(): int
    {
        if (! (bool) setting('seo.sitemap_enabled', true)) {
            return 0;
        }

        $urls = $this->urls();
        $xml = $this->xml($urls);

        $path = public_path('sitemap.xml');
        $tmp = $path . '.tmp';

        if (file_put_contents($tmp, $xml) === false) {
            return 0;
        }

        if (! @rename($tmp, $path)) {
            @unlink($tmp);

            return 0;
        }

        Cache::forever(self::LAST_GENERATED_KEY, now()->toIso8601String());

        return count($urls);
    }

    /** @return list<array{loc:string,lastmod:?string,changefreq:string,priority:string}> */
    public function urls(): array
    {
        $urls = [];
        $add = function (string $loc, mixed $lastmod = null, string $changefreq = 'weekly', string $priority = '0.7') use (&$urls): void {
            $urls[] = [
                'loc' => $loc,
                'lastmod' => $lastmod ? \Illuminate\Support\Carbon::parse($lastmod)->toAtomString() : null,
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        };

        $homeLastmod = max(
            (string) Property::query()->max('updated_at'),
            (string) Project::query()->max('updated_at'),
            (string) Post::query()->max('updated_at')
        );
        $add(url('/'), $homeLastmod ?: now(), 'daily', '1.0');

        foreach ([
            ['/properties', '0.9', 'daily'],
            ['/projects', '0.9', 'weekly'],
            ['/agents', '0.8', 'weekly'],
            ['/blog', '0.8', 'weekly'],
            ['/careers', '0.6', 'monthly'],
            ['/about-us', '0.6', 'monthly'],
            ['/contact-us', '0.6', 'monthly'],
            ['/faqs', '0.5', 'monthly'],
            ['/privacy-policy', '0.3', 'yearly'],
            ['/terms-and-conditions', '0.3', 'yearly'],
        ] as [$path, $priority, $frequency]) {
            $add(url($path), null, $frequency, $priority);
        }

        Property::query()
            ->with('translations')
            ->whereIn('status', ['published', 'sold'])
            ->orderBy('id')
            ->chunk(200, function ($properties) use ($add): void {
                foreach ($properties as $property) {
                    $slug = $property->translations->firstWhere('locale', 'ar')?->slug
                        ?: $property->reference_code;

                    if ($slug) {
                        $add(url('/properties/' . $slug), $property->updated_at, 'weekly', '0.8');
                    }
                }
            });

        Project::query()
            ->with('translations')
            ->where('status', 'published')
            ->orderBy('id')
            ->chunk(200, function ($projects) use ($add): void {
                foreach ($projects as $project) {
                    $slug = $project->translations->firstWhere('locale', 'ar')?->slug;
                    if ($slug) {
                        $add(url('/projects/' . $slug), $project->updated_at, 'weekly', '0.7');
                    }
                }
            });

        Post::query()
            ->with('translations')
            ->where('status', 'published')
            ->orderBy('id')
            ->chunk(200, function ($posts) use ($add): void {
                foreach ($posts as $post) {
                    $slug = $post->translations->firstWhere('locale', 'ar')?->slug;
                    if ($slug) {
                        $add(url('/blog/' . $slug), $post->updated_at, 'monthly', '0.6');
                    }
                }
            });

        Career::query()
            ->with('translations')
            ->where('status', 'published')
            ->orderBy('id')
            ->chunk(200, function ($careers) use ($add): void {
                foreach ($careers as $career) {
                    $slug = $career->translations->firstWhere('locale', 'ar')?->slug;
                    if ($slug) {
                        $add(url('/careers/' . $slug), $career->updated_at, 'monthly', '0.5');
                    }
                }
            });

        AgentProfile::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->chunk(200, function ($agents) use ($add): void {
                foreach ($agents as $agent) {
                    if ($agent->slug) {
                        $add(url('/agents/' . $agent->slug), $agent->updated_at, 'monthly', '0.6');
                    }
                }
            });

        return $urls;
    }

    /** @param list<array{loc:string,lastmod:?string,changefreq:string,priority:string}> $urls */
    private function xml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . $this->escape($url['loc']) . "</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . $this->escape($url['lastmod']) . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        return $xml . '</urlset>' . "\n";
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
