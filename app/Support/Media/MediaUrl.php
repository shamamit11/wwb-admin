<?php

namespace App\Support\Media;

class MediaUrl
{
    public function resolve(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $path;
        }

        $baseUrl = rtrim((string) config('widewebblog.media.base_url'), '/');

        if ($baseUrl === '') {
            return '/'.ltrim($path, '/');
        }

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
