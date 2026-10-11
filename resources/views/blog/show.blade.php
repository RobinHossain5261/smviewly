@extends('layouts.app')
@section('title', $post->title.' | '.$siteSettings->brand_name)
@section('description', $post->seo_description)
@section('canonical', route('blog.show', $post->slug))
@section('og_type', 'article')
@section('og_image', $post->image_url)
@section('content')
<div class="sm-home sm-article-page">
    <header class="sm-hub-hero"><div class="sm-container">
        <nav class="sm-hub-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><i class="ph ph-caret-right" aria-hidden="true"></i><a href="{{ route('blog.index') }}">Blog</a><i class="ph ph-caret-right" aria-hidden="true"></i><span>{{ $post->category?->name ?? 'Guide' }}</span></nav>
        <p class="sm-kicker">{{ $post->category?->name ?? 'From the journal' }}</p><h1>{{ $post->title }}</h1>
        <div class="sm-article-meta"><span>{{ $siteSettings->brand_name }} editorial</span><time datetime="{{ $post->updated_at?->toAtomString() }}">Updated {{ $post->updated_at?->format('M d, Y') }}</time><span>{{ $readingMinutes }} min read</span></div>
    </div></header>
    <div class="sm-section-band"><div class="sm-container sm-article-layout">
        <article class="sm-article-main"><img class="sm-article-cover" src="{{ $post->image_url }}" alt="{{ $post->title }}" width="1000" height="650" fetchpriority="high" decoding="async"><div class="sm-article-copy">{!! $articleContent !!}</div><div class="sm-article-end"><a href="{{ route('blog.index') }}"><i class="ph ph-arrow-left" aria-hidden="true"></i> Back to all articles</a><a href="{{ route('contact') }}">Have a question? <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div></article>
        <aside class="sm-article-sidebar" aria-label="Article navigation and related resources">
            @if(count($tableOfContents))
            <details class="sm-article-toc sm-sidebar-card" open><summary>In this guide <i class="ph ph-caret-down" aria-hidden="true"></i></summary><nav aria-label="Table of contents">@foreach($tableOfContents as $entry)<a href="#{{ $entry['id'] }}">{{ $entry['title'] }}</a>@endforeach</nav></details>
            @endif
            <div class="sm-sidebar-card sm-sidebar-tools"><p class="sm-kicker">Put it into practice</p><h2>Explore the public side.</h2><p>Choose the tool that matches your link and review what is available.</p>@foreach(\App\Support\PlatformCatalog::all() as $platformSlug => $platformInfo)<a href="{{ route('viewer.tool', ['platform' => $platformSlug]) }}"><i class="ph {{ $platformInfo['icon'] }}" aria-hidden="true"></i>{{ $platformInfo['name'] }} tools<i class="ph ph-arrow-up-right" aria-hidden="true"></i></a>@endforeach</div>
            @if($relatedPosts->isNotEmpty())<div class="sm-sidebar-card sm-sidebar-related"><h2>Related reading</h2>@foreach($relatedPosts as $related)<a href="{{ route('blog.show', $related->slug) }}">{{ $related->title }}<i class="ph ph-arrow-up-right" aria-hidden="true"></i></a>@endforeach</div>@endif
        </aside>
    </div></div>
</div>
@endsection
@push('ld-json')
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $post->title, 'datePublished' => $post->created_at?->toAtomString(), 'dateModified' => $post->updated_at?->toAtomString(), 'image' => $post->image_url, 'author' => ['@type' => 'Organization', 'name' => $siteSettings->brand_name], 'publisher' => ['@type' => 'Organization', 'name' => $siteSettings->brand_name], 'mainEntityOfPage' => route('blog.show', $post->slug), 'description' => $post->seo_description], JSON_HEX_TAG|JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
