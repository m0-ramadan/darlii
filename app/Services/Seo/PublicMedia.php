<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Storage;

/** Resolve public media without publishing URLs for missing local files. */
class PublicMedia
{
    public function url(?string $path, string $disk = 'public'): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            $host = parse_url($path, PHP_URL_HOST);
            if (! in_array($host, ['darlii.com', 'www.darlii.com', parse_url((string) config('app.url'), PHP_URL_HOST)], true)) {
                return $path;
            }
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = ltrim(rawurldecode($path), '/');
        if (in_array('..', explode('/', $path), true)) {
            return null;
        }

        if (str_starts_with($path, 'storage/')) {
            $relative = substr($path, 8);
            return Storage::disk('public')->exists($relative) ? asset('storage/' . $relative) : null;
        }

        if ($disk === 'legacy') {
            $path = str_starts_with($path, 'legacy/') ? $path : 'legacy/' . $path;
            return is_file(public_path($path)) ? asset($path) : null;
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        return Storage::disk('public')->exists($path) ? asset('storage/' . $path) : null;
    }
}
