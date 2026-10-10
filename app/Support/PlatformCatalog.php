<?php

namespace App\Support;

class PlatformCatalog
{
    public static function all(): array
    {
        return config('platforms', []);
    }

    public static function slugs(): array
    {
        return array_keys(self::all());
    }

    public static function exists(string $platform): bool
    {
        return array_key_exists(strtolower($platform), self::all());
    }

    public static function get(string $platform): ?array
    {
        $platform = strtolower($platform);

        return self::all()[$platform] ?? null;
    }

    public static function tools(string $platform): array
    {
        return self::get($platform)['tools'] ?? [];
    }

    public static function tool(string $platform, string $slug): ?array
    {
        foreach (self::tools($platform) as $tool) {
            if (($tool['slug'] ?? '') === $slug) {
                return self::enrich($tool);
            }
        }

        return null;
    }

    /**
     * Fill intent / focus / CTA so the page copy and result UI match the tool.
     */
    public static function enrich(array $tool): array
    {
        $slug = $tool['slug'] ?? '';

        $tool['intent'] ??= str_contains($slug, 'downloader') || str_contains($slug, 'saver')
            ? 'download'
            : (str_contains($slug, 'search') ? 'search' : 'view');

        $tool['focus'] ??= match (true) {
            str_contains($slug, 'avatar') => 'avatar',
            str_contains($slug, 'reel') => 'reel',
            str_contains($slug, 'photo') => 'photo',
            str_contains($slug, 'video') => 'video',
            str_contains($slug, 'comment') => 'comments',
            str_contains($slug, 'highlight') => 'highlights',
            str_contains($slug, 'stor') => 'stories',
            str_contains($slug, 'follower') || str_contains($slug, 'following') || str_contains($slug, 'related') || str_contains($slug, 'employee') => 'people',
            str_contains($slug, 'hashtag') || str_contains($slug, 'tag') => 'tags',
            str_contains($slug, 'profile') || str_contains($slug, 'page-viewer') || str_contains($slug, 'company-viewer') => 'profile',
            default => 'media',
        };

        $tool['cta'] ??= match ($tool['intent']) {
            'download' => 'Download',
            'search' => 'Search',
            default => 'View',
        };

        $tool['download_label'] ??= match ($tool['focus']) {
            'reel' => 'Download Reel',
            'photo' => 'Download Photo',
            'video' => 'Download Video',
            'avatar' => 'Download Avatar',
            'stories' => 'Download Story',
            default => 'Download',
        };

        return $tool;
    }

    public static function defaultTool(string $platform): ?array
    {
        $tools = self::tools($platform);

        foreach ($tools as $tool) {
            if (! empty($tool['featured'])) {
                return self::enrich($tool);
            }
        }

        return isset($tools[0]) ? self::enrich($tools[0]) : null;
    }

    public static function placeholder(string $platform, ?string $slug = null): string
    {
        $meta = self::get($platform);
        $tool = $slug ? self::tool($platform, $slug) : null;

        return $tool['placeholder'] ?? $meta['placeholder'] ?? 'Enter a username or URL';
    }

    public static function hubTools(string $platform): array
    {
        $meta = self::get($platform);
        if (! $meta) {
            return [];
        }

        return array_map(function (array $tool) use ($meta, $platform) {
            return [
                'name' => $meta['name'].' '.$tool['name'],
                'slug' => $tool['slug'],
                'icon' => $tool['icon'] ?? 'ph-eye',
                'desc' => $tool['desc'] ?? '',
                'platform' => $platform,
            ];
        }, $meta['tools']);
    }

    public static function similar(string $platform, string $slug, int $limit = 8): array
    {
        $items = array_values(array_filter(
            self::hubTools($platform),
            fn (array $tool) => $tool['slug'] !== $slug
        ));

        return array_slice($items, 0, $limit);
    }

    public static function featured(): array
    {
        $out = [];
        foreach (self::all() as $slug => $meta) {
            foreach ($meta['tools'] as $tool) {
                if (! empty($tool['featured'])) {
                    $out[] = [
                        'platform' => $slug,
                        'slug' => $tool['slug'],
                        'name' => $meta['name'].' '.$tool['name'],
                        'icon' => $tool['icon'] ?? $meta['icon'],
                        'color' => $meta['color'],
                        'bg' => $meta['bg'],
                    ];
                }
            }
        }

        return $out;
    }
}
