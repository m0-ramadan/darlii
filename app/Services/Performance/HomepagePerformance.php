<?php

namespace App\Services\Performance;

class HomepagePerformance
{
    public function optimize(string $html): string
    {
        // The Arabic bundle includes Bootstrap RTL; English retains its own styles.
        if (app()->getLocale() !== 'ar' || ! is_file(resource_path('performance/homepage.json'))) {
            return $html;
        }
        $manifest = json_decode(file_get_contents(resource_path('performance/homepage.json')), true);
        if (! is_file(public_path($manifest['bundle'])) || ! is_file(public_path($manifest['critical']))) {
            return $html;
        }

        $html = str_replace(array_keys($manifest['images'] ?? []), array_values($manifest['images'] ?? []), $html);

        $icons = json_decode(file_get_contents(resource_path('performance/lucide-icons.json')), true);
        $unknown = false;
        $html = preg_replace_callback('~<i\b([^>]*\bdata-lucide=["\']([a-z0-9-]+)["\'][^>]*)>\s*</i>~i', function (array $match) use ($icons, &$unknown): string {
            $name = $match[2];
            if (! isset($icons[$name])) {
                $unknown = true;
                return $match[0];
            }
            $attributes = preg_replace('~\s*data-lucide=["\'][^"\']*["\']~i', '', $match[1]);
            if (preg_match('~\bclass=(["\'])(.*?)\1~i', $attributes)) {
                $attributes = preg_replace('~\bclass=(["\'])(.*?)\1~i', 'class=$1lucide lucide-' . $name . ' $2$1', $attributes);
            } else {
                $attributes .= ' class="lucide lucide-' . e($name) . '"';
            }
            $children = '';
            foreach ($icons[$name] as [$tag, $attrs]) {
                $children .= '<' . $tag;
                foreach ($attrs as $key => $value) {
                    $children .= ' ' . $key . '="' . e((string) $value) . '"';
                }
                $children .= '/>';
            }
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"' . $attributes . '>' . $children . '</svg>';
        }, $html) ?? $html;
        if (! $unknown) {
            $html = preg_replace('~<script\b[^>]*src=["\'][^"\']*/plugins/lucide/lucide\.min\.js[^"\']*["\'][^>]*>\s*</script>~i', '', $html) ?? $html;
        }

        // Keep the complete stylesheet as a non-blocking fallback. No HTML caching:
        // auth links, CSRF tokens and visitor preferences stay request-specific.
        $fonts = json_decode(file_get_contents(resource_path('performance/fonts.json')), true);
        $bodyFont = e($fonts['400']);
        $headingFont = e($fonts['800']);
        $replaced = false;
        $critical = file_get_contents(public_path($manifest['critical']));
        $html = preg_replace_callback('~<link\b[^>]*rel=["\']stylesheet["\'][^>]*>~i', function (array $match) use ($manifest, $critical, $bodyFont, $headingFont, &$replaced): string {
            preg_match('~\bhref=["\']([^"\']+)["\']~i', $match[0], $href);
            $path = parse_url(html_entity_decode($href[1] ?? ''), PHP_URL_PATH);
            if (! in_array($path, $manifest['styles'], true)) {
                return $match[0];
            }
            if ($replaced) {
                return '';
            }
            $replaced = true;
            $bundle = e(asset(ltrim($manifest['bundle'], '/')));
            return '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . $bodyFont . '">'
                . '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . $headingFont . '">'
                . '<style id="darli-home-critical">' . $critical . '</style>'
                . '<link id="darli-home-full-css" rel="stylesheet" href="' . $bundle . '" media="print" onload="this.media=\'all\'">'
                . '<noscript><link rel="stylesheet" href="' . $bundle . '"></noscript>';
        }, $html) ?? $html;

        $html = preg_replace('~<script\b[^>]*type=["\']speculationrules["\'][^>]*>.*?</script>~is', '', $html) ?? $html;
        $html = $this->imageDimensions($html);

        return $html;
    }

    private function imageDimensions(string $html): string
    {
        return preg_replace_callback('~<img\b[^>]*>~i', static function (array $match): string {
            $tag = $match[0];
            $width = preg_match('~\swidth\s*=~i', $tag);
            $height = preg_match('~\sheight\s*=~i', $tag);
            if ($width && $height) {
                return $tag;
            }
            preg_match('~\ssrc=["\']([^"\']+)["\']~i', $tag, $src);
            $url = html_entity_decode($src[1] ?? '');
            $host = parse_url($url, PHP_URL_HOST);
            if ($host && ! in_array($host, ['darlii.com', 'www.darlii.com', request()->getHost(), parse_url((string) config('app.url'), PHP_URL_HOST)], true)) {
                return $tag;
            }
            $path = rawurldecode((string) parse_url($url, PHP_URL_PATH));
            if ($path === '' || str_contains($path, '..') || ! is_file(public_path($path))) {
                return $tag;
            }
            $size = @getimagesize(public_path($path));
            if (! $size && str_ends_with(strtolower($path), '.svg')) {
                $svg = file_get_contents(public_path($path), false, null, 0, 4096);
                if (preg_match('~viewBox=["\']\s*[-.\d]+\s+[-.\d]+\s+([.\d]+)\s+([.\d]+)["\']~i', $svg, $box)) {
                    $size = [(float) $box[1], (float) $box[2]];
                }
            }
            if (! $size || $size[0] <= 0 || $size[1] <= 0) {
                return $tag;
            }
            [$w, $h] = $size;
            if ($height && preg_match('~\sheight=["\']?(\d+)~i', $tag, $fixed)) {
                $w = (int) round((int) $fixed[1] * $w / $h);
            } elseif ($width && preg_match('~\swidth=["\']?(\d+)~i', $tag, $fixed)) {
                $h = (int) round((int) $fixed[1] * $h / $w);
            }
            $dimensions = ($width ? '' : ' width="' . $w . '"') . ($height ? '' : ' height="' . $h . '"');
            return preg_replace('~<img\b~i', '<img' . $dimensions, $tag, 1) ?? $tag;
        }, $html) ?? $html;
    }
}
