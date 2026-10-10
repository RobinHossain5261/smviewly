<?php

namespace App\Support;

class BrandAssets
{
    public static function url(?string $path, string $fallback): string
    {
        $path = trim($path ?? '');

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');
        if ($path !== '') {
            $publicPath = str_starts_with($path, 'images/') || str_starts_with($path, 'storage/')
                ? $path : 'storage/'.$path;
            if (is_file(public_path($publicPath))) {
                return asset($publicPath);
            }
        }

        return asset($fallback).'?v='.filemtime(public_path($fallback));
    }
}
