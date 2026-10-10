<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the blog posts.
     */
    public function index(Request $request)
    {
        $query = Post::published()->with('category')->latest();

        // Optional category filter
        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        $featuredPost = (clone $query)->first();
        $posts = $query->paginate(6)->withQueryString();
        $categories = Category::withCount(['posts' => fn ($query) => $query->published()])->get();

        return view('blog.index', compact('posts', 'categories', 'featuredPost'));
    }

    /**
     * Display the specified blog post.
     */
    public function show($slug)
    {
        $post = Post::published()->with('category')->where('slug', $slug)->firstOrFail();
        $relatedPosts = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(3)
            ->get();

        $tableOfContents = [];
        $articleContent = preg_replace_callback('/<h2\b[^>]*>(.*?)<\/h2>/is', function ($match) use (&$tableOfContents) {
            $id = 'article-section-'.(count($tableOfContents) + 1);
            $tableOfContents[] = ['id' => $id, 'title' => html_entity_decode(strip_tags($match[1]), ENT_QUOTES, 'UTF-8')];
            return '<h2 id="'.$id.'">'.$match[1].'</h2>';
        }, $post->content);
        $wordCount = str_word_count(html_entity_decode(strip_tags(preg_replace('/<[^>]+>/', ' ', $post->content)), ENT_QUOTES, 'UTF-8'));
        $readingMinutes = max(1, (int) ceil($wordCount / 220));

        return view('blog.show', compact('post', 'relatedPosts', 'articleContent', 'tableOfContents', 'readingMinutes'));
    }
}
