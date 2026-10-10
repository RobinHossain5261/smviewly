<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Support\SitemapBuilder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogEditorialRevisionSeeder extends Seeder
{
    public function run(): void
    {
        // Explicit editorial revision only; ordinary reseeding preserves admin edits.
        $articles = json_decode(file_get_contents(resource_path('content/blog-editorial.json')), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($articles) {
            foreach ($articles as $article) {
                $post = Post::where('slug', $article['slug'])->first();
                if (! $post) {
                    continue;
                }
                $html = '';
                foreach ($article['sections'] as [$heading, $body]) {
                    $html .= '<h2>'.e($heading).'</h2><p>'.$body.'</p>';
                }
                $count = str_word_count(strip_tags(str_replace(['</h2>', '</p>'], ' ', $html)));
                if ($count < 1000 || $count > 1500) {
                    throw new \RuntimeException('Article length is outside the requested range: '.$article['slug']);
                }
                $post->update(['title' => $article['title'], 'content' => $html]);
                $this->command?->info($article['slug'].' — '.$count.' words');
            }
        });
        SitemapBuilder::flush();
    }
}
