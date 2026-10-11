<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class SitemapBuilder
{
    public const CACHE_KEY = 'smviewly.sitemap.payload';

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY.'.pages');
        Cache::forget(self::CACHE_KEY.'.posts');
    }

    /**
     * @return list<array{loc: string, lastmod?: string, changefreq: string, priority: string}>
     */
    public static function pages(): array
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => route('tools'), 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => route('contact'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => route('blog.index'), 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => route('faqs'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => route('privacy'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => route('terms'), 'changefreq' => 'yearly', 'priority' => '0.3'],
        ];

        $latestPost = Post::published()->latest('updated_at')->first();
        if ($latestPost?->updated_at) {
            foreach ($urls as &$entry) {
                if ($entry['loc'] === route('blog.index')) {
                    $entry['lastmod'] = $latestPost->updated_at->toAtomString();
                }
            }
            unset($entry);
        }

        foreach (PlatformCatalog::all() as $slug => $meta) {
            $urls[] = [
                'loc' => route('viewer.tool', ['platform' => $slug]),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];

            foreach ($meta['tools'] as $tool) {
                $urls[] = [
                    'loc' => route('viewer.tool', ['platform' => $slug, 'tool' => $tool['slug']]),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        }

        return $urls;
    }

    /**
     * Every published blog post. Drafts are never included.
     *
     * @return list<array{loc: string, lastmod?: string, changefreq: string, priority: string}>
     */
    public static function posts(): array
    {
        $urls = [];

        foreach (Post::published()->whereNotNull('slug')->where('slug', '!=', '')->latest('updated_at')->get() as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        return $urls;
    }

    /**
     * @return list<array{loc: string, lastmod?: string, changefreq: string, priority: string}>
     */
    public static function all(): array
    {
        return array_merge(self::pages(), self::posts());
    }

    public static function latestPostModified(): ?string
    {
        $post = Post::published()->latest('updated_at')->first();

        return $post?->updated_at?->toAtomString();
    }
}
