@extends('layouts.app')
@section('title', $seoTitle)
@section('description', $description)
@section('keywords', $keywords)
@section('canonical', route('viewer.tool', ['platform' => $platform, 'tool' => $slug]))
@php
    $intent = $toolConfig['intent'] ?? 'view';
    $cta = $toolConfig['cta'] ?? 'View';
    $toolQuestions = $pageContent['questions'];
@endphp
@section('content')
<div class="sm-home sm-tool-page">
    <section class="sm-hub-hero">
        <div class="sm-container">
            <nav class="sm-hub-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><i class="ph ph-caret-right" aria-hidden="true"></i><a href="{{ route('viewer.tool', ['platform' => $platform]) }}">{{ $meta['name'] }} tools</a><i class="ph ph-caret-right" aria-hidden="true"></i><span>{{ $toolConfig['name'] }}</span></nav>
            <div class="sm-tool-intro"><div><p class="sm-kicker">{{ $meta['name'] }} / {{ $intent === 'download' ? 'Media downloader' : ($intent === 'search' ? 'Public search' : 'Public content viewer') }}</p><h1>{{ $title }}</h1><p class="sm-hub-description">{{ $pageContent['intro'] }}</p></div><div class="sm-tool-guidance"><span class="sm-platform-icon"><i class="ph {{ $icon }}" aria-hidden="true"></i></span><div><strong>What to enter</strong><p>{{ $pageContent['input'] }}</p></div></div></div>
            <div class="sm-search-panel" id="main-search-container">
                <form id="hero-search-form" action="{{ route('viewer.search') }}" method="POST">
                    @csrf
                    <input type="hidden" name="platform" value="{{ $platform }}">
                    <input type="hidden" name="tool" id="active-tool-input" value="{{ $slug }}">
                    <div class="sm-search-label"><label for="search-input">{{ $intent === 'search' ? 'Enter your search' : 'Enter a name or paste a public link' }}</label><span class="sm-search-badge"><i class="ph ph-shield-check" aria-hidden="true"></i>Public content only</span></div>
                    <div class="sm-input-wrap"><i class="ph ph-link sm-input-icon" aria-hidden="true"></i><input id="search-input" type="text" name="username" value="{{ old('username') }}" placeholder="{{ $placeholder }}" required maxlength="500" autocomplete="off"><button type="button" id="sm-paste" aria-label="Paste from clipboard"><i class="ph ph-clipboard-text" aria-hidden="true"></i><span>Paste</span></button></div>
                    <div class="sm-search-bottom"><div class="sm-security"><span class="captcha-img">{!! captcha_img('easy') !!}</span><button type="button" id="reload-captcha" aria-label="Refresh security code"><i class="ph ph-arrows-clockwise" aria-hidden="true"></i></button><label class="sm-sr-only" for="captcha-input">Security code</label><input id="captcha-input" name="captcha" type="text" required placeholder="Security code" autocomplete="off"></div><button class="sm-button" type="submit" id="search-submit-btn"><span>{{ $cta }} {{ $intent === 'search' ? 'content' : ($intent === 'download' ? 'media' : 'content') }}</span><i class="ph ph-arrow-right" aria-hidden="true"></i></button></div>
                    <div id="captcha-error-container" class="sm-error {{ $errors->any() ? '' : 'hidden' }}" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i><span id="captcha-error-text">{{ $errors->first() }}</span></div>
                    <div id="search-loading" class="hidden sm-loading" role="status"><span class="sm-spinner"></span><span>Looking up available public content…</span></div>
                </form>
                <div class="sm-search-foot"><span><i class="ph ph-info" aria-hidden="true"></i> Results depend on source availability.</span><a href="#tool-how-title">How it works <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div>
            </div>
            <div class="sm-reassurance"><span><i class="ph ph-user-circle" aria-hidden="true"></i>No social login</span><span><i class="ph ph-browser" aria-hidden="true"></i>No installation needed</span></div>
            <div id="live-results" class="hidden sm-results" aria-live="polite"></div>
        </div>
    </section>
    <div class="sm-section-band sm-band-how"><section class="sm-container sm-how" aria-labelledby="tool-how-title"><div class="sm-how-intro"><p class="sm-kicker">Simple & Transparent</p><h2 id="tool-how-title">Using {{ $toolConfig['name'] }}.</h2><p>Start with the right input. Review only what {{ $meta['name'] }} makes publicly available.</p></div><div class="sm-steps"><article class="sm-step-card"><span class="sm-step-number">STEP 01</span><span class="sm-step-icon"><i class="ph ph-link" aria-hidden="true"></i></span><h3>Enter your input</h3><p>{{ $pageContent['input'] }} Example: {{ $pageContent['example'] }}. {{ $pageContent['example_note'] }}</p></article><article class="sm-step-card"><span class="sm-step-number">STEP 02</span><span class="sm-step-icon"><i class="ph ph-shield-check" aria-hidden="true"></i></span><h3>Verify and submit</h3><p>Enter the security code shown beside the field, then submit your input to start the lookup.</p></article><article class="sm-step-card"><span class="sm-step-number">STEP 03</span><span class="sm-step-icon"><i class="ph ph-eye" aria-hidden="true"></i></span><h3>View the results</h3><p>{{ $pageContent['result'] }}</p></article></div></section></div>
    @if(count($similar))
    <div class="sm-section-band"><section class="sm-container" aria-labelledby="related-tools-title"><div class="sm-section-heading"><div><p class="sm-kicker">More from {{ $meta['name'] }}</p><h2 id="related-tools-title">Need a different view?</h2><p class="sm-section-sub">Other {{ $meta['name'] }} tools for your next lookup.</p></div><a class="sm-text-link" href="{{ route('viewer.tool', ['platform' => $platform]) }}">All {{ $meta['name'] }} tools <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div><div class="sm-tool-grid">
        @foreach($similar as $related)
        @php $relatedConfig = \App\Support\PlatformCatalog::tool($platform, $related['slug']); @endphp
        <a class="sm-tool-card" href="{{ route('viewer.tool', ['platform' => $platform, 'tool' => $related['slug']]) }}" data-tool-platform="{{ $platform }}"><div class="sm-tool-top"><span class="sm-platform-icon"><i class="ph {{ $related['icon'] }}" aria-hidden="true"></i></span><div class="sm-tool-heading"><span class="sm-tool-platform-name">{{ $meta['name'] }}</span><h3>{{ $relatedConfig['name'] }}</h3></div></div><p>{{ $related['desc'] }}</p><span class="sm-tool-bottom"><span class="sm-tool-type">{{ $relatedConfig['intent'] === 'download' ? 'Media downloader' : 'Public content tool' }}</span><span class="sm-tool-action">Open tool</span><span class="sm-tool-arrow"><i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></span></a>
        @endforeach
    </div></section></div>
    @endif
    <div class="sm-section-band sm-band-faq"><section class="sm-container sm-faq" aria-labelledby="tool-faq-title"><div class="sm-faq-sidebar"><p class="sm-kicker">Common questions</p><h2 id="tool-faq-title">About {{ $toolConfig['name'] }}.</h2><p class="sm-faq-sub">Input guidance, availability, and responsible use.</p><a class="sm-text-link" href="{{ route('contact') }}">Contact support <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div><div class="sm-faq-list">
        @foreach($toolQuestions as $index => [$question, $answer])
        <div class="sm-faq-item {{ $index === 0 ? 'is-open' : '' }}"><button class="sm-faq-question" type="button" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="tool-faq-{{ $index }}"><span>{{ $question }}</span><span class="sm-faq-toggle"><i class="ph ph-plus" aria-hidden="true"></i></span></button><div class="sm-faq-collapse" id="tool-faq-{{ $index }}"><div class="sm-faq-body"><p>{{ $answer }}</p></div></div></div>
        @endforeach
    </div></section></div>
</div>
@endsection
@push('scripts')
@include('partials.live-search-script')
<script>
    document.getElementById('sm-paste').addEventListener('click', async () => {
        const input = document.getElementById('search-input');
        try { input.value = (await navigator.clipboard.readText()).trim(); }
        catch (_) { const box = document.getElementById('captcha-error-container'); document.getElementById('captcha-error-text').textContent = 'Paste your link directly into the field using your device paste command.'; box.classList.remove('hidden'); }
        input.focus();
    });
</script>
@endpush
@push('ld-json')
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')], ['@type' => 'ListItem', 'position' => 2, 'name' => $meta['name'].' tools', 'item' => route('viewer.tool', ['platform' => $platform])], ['@type' => 'ListItem', 'position' => 3, 'name' => $toolConfig['name'], 'item' => route('viewer.tool', ['platform' => $platform, 'tool' => $slug])]]], JSON_HEX_TAG|JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
