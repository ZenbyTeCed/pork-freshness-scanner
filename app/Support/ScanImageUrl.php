<?php

namespace App\Support;

class ScanImageUrl
{
    public static function fromRecord(array $record, ?string $fallback = null): ?string
    {
        $imagePath = self::cleanPath($record['image_path'] ?? null);

        if ($imagePath !== null) {
            return "/storage/{$imagePath}";
        }

        $imageUrl = self::cleanUrl($record['image_url'] ?? null);

        if ($imageUrl === null) {
            return $fallback;
        }

        $path = parse_url($imageUrl, PHP_URL_PATH);

        if (is_string($path) && str_starts_with($path, '/storage/')) {
            return $path;
        }

        return $imageUrl;
    }

    private static function cleanPath(mixed $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        if ($path === '' || strtoupper($path) === 'N/A') {
            return null;
        }

        return ltrim($path, '/');
    }

    private static function cleanUrl(mixed $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $url = trim($url);

        if ($url === '' || strtoupper($url) === 'N/A') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return null;
        }

        return $url;
    }
}
