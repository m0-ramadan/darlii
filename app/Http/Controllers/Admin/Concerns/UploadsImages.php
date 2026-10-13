<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shared image upload handling for the CMS. The destination disk comes from
 * the storage settings page (public / local / s3), so switching storage
 * providers does not require a code change.
 */
trait UploadsImages
{
    protected function uploadDisk(): string
    {
        $disk = (string) setting('storage.default_disk', 'public');

        return in_array($disk, ['public', 'local', 's3'], true) ? $disk : 'public';
    }

    /**
     * Store an uploaded image on the configured disk.
     *
     * Note: UploadedFile has no validate() method in this Laravel version, so
     * every caller MUST validate the request itself (see the FormRequest /
     * controller rules) before calling this helper.
     */
    protected function uploadImage(UploadedFile $file, string $folder = 'uploads'): ?string
    {
        if (! $file->isValid()) {
            return null;
        }

        return $file->store($folder . '/' . now()->format('Y/m'), $this->uploadDisk());
    }

    protected function deleteUpload(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http')) {
            return;
        }

        Storage::disk($this->uploadDisk())->delete($path);
        Storage::disk('public')->delete($path);
    }

    /** Public URL for any stored path (works for local, public and s3 disks). */
    protected function uploadUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    protected function uniqueSlug(string $value, string $modelClass, string $column = 'slug', ?int $ignoreId = null): string
    {
        $slug = Str::slug($value) ?: Str::random(8);
        $base = $slug;
        $i = 2;

        while ($modelClass::query()
            ->where($column, $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
