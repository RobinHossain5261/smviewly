@extends('layouts.app')
@section('title', 'Social Media Tips & Guides | '.$siteSettings->brand_name)
@section('description', 'Practical guides to public Facebook pages, X media, Threads posts and LinkedIn profiles. Learn how to use SM Viewer and save available media responsibly.')
@section('content')
<div class="sm-home sm-blog-page">
    <section class="sm-hub-hero">
        <div class="sm-container">
            <nav class="sm-hub-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><i class="ph ph-caret-right" aria-hidden="true"></i><span>Blog</span></nav>
            <div class="sm-blog-banner-grid"><div>
            <p class="sm-kicker">From the journal</p>
            <h1>Practical guides.<br><em>A little more clarity.</em></h1>
            <p class="sm-hub-description">Public content, useful tools, and the details worth knowing. Find guides to browsing social platforms, working with available media, and respecting privacy.</p>
            </div>
            @if($featuredPost)
            <a class="sm-blog-featured" href="{{ route('blog.show', $featuredPost->slug) }}">
                <img src="{{ $featuredPost->image_url }}" alt="{{ $featuredPost->title }}" width="600" height="400" fetchpriority="high" decoding="async">
                <div><span class="sm-kicker">Latest in {{ $featuredPost->category?->name ?? 'the journal' }}</span><h2>{{ $featuredPost->title }}</h2><span class="sm-usecase-link">Read the article <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></div>
            </a>
            @else
            <div class="sm-blog-featured-empty"><i class="ph ph-book-open" aria-hidden="true"></i><h2>A place for practical answers.</h2><p>New published guides will appear here.</p></div>
            @endif
            </div>
        </div>
    </section>
    <div class="sm-section-band">
        <section class="sm-container" aria-labelledby="blog-list-title">
            <div class="sm-section-heading"><div><p class="sm-kicker">The reading list</p><h2 id="blog-list-title">{{ request('category') ? ($categories->firstWhere('slug', request('category'))?->name ?? 'Latest articles') : 'Latest articles' }}</h2><p class="sm-section-sub">Browse by topic, or see what is new.</p></div><span class="sm-blog-count">{{ $posts->total() }} {{ $posts->total() === 1 ? 'article' : 'articles' }}</span></div>
            <nav class="sm-blog-filters" aria-label="Filter articles by category">
                <a href="{{ route('blog.index') }}" class="{{ !request('category') ? 'is-active' : '' }}" {{ !request('category') ? 'aria-current=page' : '' }}>All articles</a>
                @foreach($categories as $category)
                <a href="{{ route('blog.index', ['category' => $category->slug]) }}" class="{{ request('category') === $category->slug ? 'is-active' : '' }}" {{ request('category') === $category->slug ? 'aria-current=page' : '' }}>{{ $category->name }}<span>{{ $category->posts_count }}</span></a>
                @endforeach
            </nav>
            @if($posts->isNotEmpty())
            <div class="sm-blog-grid">
                @foreach($posts as $post)
                <article class="sm-guide-card sm-blog-card">
                    <a class="sm-guide-image" href="{{ route('blog.show', $post->slug) }}" aria-label="Read {{ $post->title }}"><img src="{{ $post->image_url }}" alt="{{ $post->title }}" width="600" height="400" loading="lazy" decoding="async"><span class="sm-guide-badge"><i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
                    <div class="sm-guide-meta"><span class="sm-guide-category">{{ $post->category?->name ?? 'Social media guide' }}</span><time class="sm-guide-date" datetime="{{ $post->created_at?->toDateString() }}">{{ $post->created_at?->format('M d, Y') }}</time></div>
                    <h3><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 180) }}</p>
                    <a class="sm-guide-readmore" href="{{ route('blog.show', $post->slug) }}">Read guide <i class="ph ph-arrow-right" aria-hidden="true"></i></a>
                </article>
                @endforeach
            </div>
            @if($posts->hasPages())<div class="sm-blog-pagination">{{ $posts->links() }}</div>@endif
            @else
            <div class="sm-blog-empty"><i class="ph ph-newspaper" aria-hidden="true"></i><h3>No articles here yet.</h3><p>Try another topic or browse the full reading list.</p><a class="sm-principles-link" href="{{ route('blog.index') }}">Browse all articles <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div>
            @endif
        </section>
    </div>
</div>
@endsection
