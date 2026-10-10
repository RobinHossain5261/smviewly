@extends('layouts.app')
@section('title', 'Social Media Viewer & Downloader | '.$siteSettings->brand_name)
@section('description', 'Explore public Facebook pages, X media, Threads posts and LinkedIn company profiles. Preview available content and save media you have permission to download.')
@push('styles')

@endpush

@php
    $platformOrder = ['facebook', 'twitter', 'threads', 'linkedin'];
    $platformIcons = ['facebook'=>'ph-facebook-logo', 'twitter'=>'ph-x-logo', 'threads'=>'ph-at', 'linkedin'=>'ph-linkedin-logo'];
    $platformNames = ['facebook'=>'Facebook', 'twitter'=>'X / Twitter', 'threads'=>'Threads', 'linkedin'=>'LinkedIn'];
    $toolPicks = [
        ['facebook', 'page-photos'], ['facebook', 'profile-viewer'], ['facebook', 'page-viewer'],
        ['facebook', 'page-reels'], ['twitter', 'profile-viewer'], ['facebook', 'post-viewer'],
        ['threads', 'profile-viewer'], ['threads', 'photos-viewer'], ['threads', 'post-viewer'],
        ['linkedin', 'company-viewer'], ['linkedin', 'profile-viewer'], ['linkedin', 'company-posts'],
    ];
@endphp



@section('content')
<div class="sm-home">
    <!-- Hero Section -->
    <section class="sm-hero" aria-labelledby="hero-title">
        <div class="sm-hero-decoration" aria-hidden="true">
            <span class="sm-float sm-float-fb"><i class="ph-fill ph-facebook-logo"></i></span>
            <span class="sm-float sm-float-x"><i class="ph ph-x-logo"></i></span>
            <span class="sm-orbit"></span>
        </div>
        <div class="sm-container sm-hero-content">
            <div class="sm-hero-intro">
            <div class="sm-hero-copy">
            <div class="sm-eyebrow">
                <span class="sm-eyebrow-dot"></span>
                <span>Four platforms. One simple search.</span>
            </div>
            <h1 id="hero-title">Public profiles.<br><em>A closer look.</em></h1>
            <p class="sm-hero-description">View public profiles, explore stories and reels, and download available media. Start with a username or link — all from your browser.</p>
            <div class="sm-hero-benefits"><span><i class="ph ph-user-circle" aria-hidden="true"></i> No social login</span><span><i class="ph ph-browser" aria-hidden="true"></i> No app needed</span></div>
            </div>
            <div class="sm-hero-preview sm-browser-scene" aria-label="Illustration of a public social profile">
                <div class="sm-browser-halo" aria-hidden="true"></div>
                <svg class="sm-browser-path" viewBox="0 0 480 360" fill="none" aria-hidden="true"><ellipse cx="240" cy="185" rx="218" ry="116" stroke="currentColor" stroke-width="1.8" stroke-dasharray="3 7" /></svg>
                <span class="sm-scene-icon sm-scene-facebook" aria-hidden="true"><i class="ph-fill ph-facebook-logo"></i></span>
                <span class="sm-scene-icon sm-scene-link" aria-hidden="true"><i class="ph ph-link"></i></span>
                <span class="sm-scene-icon sm-scene-x" aria-hidden="true"><i class="ph ph-x-logo"></i></span>
                <div class="sm-browser-window">
                    <div class="sm-browser-chrome" aria-hidden="true"><span></span><span></span><span></span><div><b></b></div></div>
                    <div class="sm-browser-body">
                        <div class="sm-browser-profile">
                            <img class="sm-browser-avatar" src="{{ asset('images/buyer1.jpg') }}" alt="" width="44" height="44" decoding="async">
                            <div class="sm-browser-profile-lines" aria-hidden="true"><span></span><span></span><span></span></div>
                            <span class="sm-browser-status"><i class="ph-fill ph-check-circle" aria-hidden="true"></i> Public link ready</span>
                        </div>
                        <div class="sm-browser-gallery">
                            <div><img src="{{ asset('images/hero-alpine-lake.jpg') }}" alt="Alpine lake and mountains" width="700" height="467" fetchpriority="high" decoding="async"><i class="ph-fill ph-image" aria-hidden="true"></i></div>
                            <div><img src="{{ asset('images/hero-mountain-view.jpg') }}" alt="Mountain landscape" width="700" height="467" decoding="async"><i class="ph-fill ph-image" aria-hidden="true"></i></div>
                            <div><img src="{{ asset('images/hero-coast.jpg') }}" alt="Ocean waves along the coast" width="450" height="300" decoding="async"><i class="ph-fill ph-play-circle" aria-hidden="true"></i></div>
                        </div>
                        <div class="sm-browser-caption" aria-hidden="true"><span></span><span></span></div>
                        <div class="sm-preview-platforms" role="group" aria-label="Choose a platform from the preview">
                            <button type="button" data-platform="facebook" class="{{ old('platform', 'facebook') === 'facebook' ? 'is-active' : '' }}" aria-pressed="{{ old('platform', 'facebook') === 'facebook' ? 'true' : 'false' }}"><i class="ph ph-facebook-logo" aria-hidden="true"></i> Facebook</button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="sm-hero-caption"><span><i class="ph ph-sparkle" aria-hidden="true"></i> Start with a public link</span><span>Choose a platform. Bring a public link.</span></div>
            
            <div class="sm-search-panel" id="main-search-container">
                <div class="sm-platforms-wrap">
                    <div class="sm-platforms" role="group" aria-label="Choose a social platform">
                        @foreach($platformOrder as $platformSlug)
                        <button type="button" data-platform="{{ $platformSlug }}" class="{{ old('platform', 'facebook') === $platformSlug ? 'is-active' : '' }}" aria-pressed="{{ old('platform', 'facebook') === $platformSlug ? 'true' : 'false' }}">
                            <i class="ph {{ $platformIcons[$platformSlug] }}" aria-hidden="true"></i>
                            <span>{{ $platformNames[$platformSlug] }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <form id="hero-search-form" action="{{ route('viewer.search') }}" method="POST">
                    @csrf
                    <input type="hidden" name="platform" id="active-platform-input" value="{{ old('platform', 'facebook') }}">
                    <input type="hidden" name="tool" id="active-tool-input" value="{{ $platformMeta[old('platform', 'facebook')]['default_tool'] ?? $platformMeta['facebook']['default_tool'] }}">
                    
                    <div class="sm-search-label">
                        <label for="search-input">Enter profile handle or paste content link</label>
                        <span class="sm-search-badge"><i class="ph ph-shield-check" aria-hidden="true"></i> Public links only</span>
                    </div>

                    <div class="sm-input-wrap">
                        <i class="ph ph-link sm-input-icon" aria-hidden="true"></i>
                        <input type="text" id="search-input" name="username" value="{{ old('username') }}" placeholder="Paste a Facebook link or enter a username" required maxlength="500" autocomplete="off">
                        <button type="button" id="sm-paste" aria-label="Paste from clipboard">
                            <i class="ph ph-clipboard-text" aria-hidden="true"></i>
                            <span>Paste</span>
                        </button>
                    </div>

                    <div class="sm-search-bottom">
                        <div class="sm-security">
                            <span class="captcha-img">{!! captcha_img('easy') !!}</span>
                            <button type="button" id="reload-captcha" aria-label="Refresh security code" title="Get new code">
                                <i class="ph ph-arrows-clockwise" aria-hidden="true"></i>
                            </button>
                            <label class="sm-sr-only" for="captcha-input">Security code</label>
                            <input type="text" id="captcha-input" name="captcha" required placeholder="Security code" autocomplete="off">
                        </div>
                        <button class="sm-button" type="submit" id="search-submit-btn">
                            <span>Explore Content</span>
                            <i class="ph-bold ph-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div id="captcha-error-container" class="sm-error {{ $errors->any() ? '' : 'hidden' }}" role="alert">
                        <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
                        <span id="captcha-error-text">{{ $errors->first() }}</span>
                    </div>
                    <div id="search-loading" class="hidden sm-loading" role="status">
                        <span class="sm-spinner"></span>
                        <span>Finding public content. Fetching stream directly...</span>
                    </div>
                </form>

                <div class="sm-search-foot">
                    <span><i class="ph ph-info" aria-hidden="true"></i> Instant preview for supported public profiles, stories, reels and media.</span>
                    <a href="#how-it-works">How does it work? <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
                </div>
            </div>

            <div class="sm-reassurance">
                <span><i class="ph-bold ph-browser" aria-hidden="true"></i> No App Installation</span>
                <span><i class="ph-bold ph-shield-check" aria-hidden="true"></i> Public Content Only</span>
                <span><i class="ph-bold ph-devices" aria-hidden="true"></i> Works on All Devices</span>
            </div>
            <div id="live-results" class="hidden sm-results" aria-live="polite"></div>
        </div>
    </section>

    <div class="sm-section-band sm-band-platforms">
    <section class="sm-container sm-platform-overview" aria-labelledby="platform-overview-title">
        <div class="sm-section-heading"><div><p class="sm-kicker">Explore by platform</p><h2 id="platform-overview-title">Choose where to start.</h2><p class="sm-section-sub">Choose a network to see its available public profile, post, and media tools.</p></div></div>
        <div class="sm-platform-overview-grid">
            @foreach($platformOrder as $platformSlug)
            @php $platformInfo = \App\Support\PlatformCatalog::get($platformSlug); @endphp
            <a class="sm-platform-overview-card" href="{{ route('viewer.tool', ['platform' => $platformSlug]) }}">
                <span class="sm-platform-icon"><i class="ph {{ $platformIcons[$platformSlug] }}" aria-hidden="true"></i></span>
                <h3>{{ $platformNames[$platformSlug] }} tools</h3>
                <p>{{ $platformInfo['short'] }}</p>
                <span>{{ count($platformInfo['tools']) }} tools <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span>
            </a>
            @endforeach
        </div>
      
    </section>
    </div>

    <!-- Why Choose smviewly Highlights Section -->
    <div class="sm-section-band sm-band-why">
    <section class="sm-why sm-container" id="why-us" aria-labelledby="why-title">
        <div class="sm-section-heading">
            <div>
                <p class="sm-kicker">Why choose us</p>
                <h2 id="why-title">Less setup. More useful information.</h2>
                <p class="sm-section-sub">Four platforms, useful tools, and a smooth experience from your first search.</p>
            </div>
        </div>
        <div class="sm-why-grid">
            <div class="sm-why-card sm-why-featured">
                <div class="sm-why-icon"><i class="ph-bold ph-user-circle-gear" aria-hidden="true"></i></div>
                <h3>No social login needed</h3>
                <p>Explore supported public content without connecting your personal social account.</p>
                <div class="sm-privacy-visual" aria-hidden="true"><span><i class="ph ph-user-circle"></i> Your social account</span><i class="ph ph-lock-key"></i><span><i class="ph ph-globe"></i> Public content</span></div>
            </div>
            <div class="sm-why-card">
                <div class="sm-why-icon"><i class="ph-bold ph-video-camera" aria-hidden="true"></i></div>
                <h3>Media worth keeping</h3>
                <p>Preview available photos and videos, then save content you have permission to download.</p>
            </div>
            <div class="sm-why-card">
                <div class="sm-why-icon"><i class="ph-bold ph-lightning" aria-hidden="true"></i></div>
                <h3>4 Platforms in One Dashboard</h3>
                <p>Facebook, X / Twitter, Threads, and LinkedIn all supported in a clean, unified interface.</p>
            </div>
            <div class="sm-why-card">
                <div class="sm-why-icon"><i class="ph-bold ph-shield-star" aria-hidden="true"></i></div>
                <h3>Zero Installation Needed</h3>
                <p>Works right in your web browser across mobile, tablet, and desktop without browser plugins or app downloads.</p>
            </div>
        </div>
    </section>
    </div>

    <!-- Toolkit Section -->
    <div class="sm-section-band sm-band-tools">
    <section class="sm-tools sm-container" id="tools" aria-labelledby="tools-title">
        <div class="sm-section-heading">
            <div>
                <p class="sm-kicker">The Toolkit</p>
                <h2 id="tools-title">Big possibilities. Simple tools.</h2>
                <p class="sm-section-sub">Choose your favorite social platform and start exploring with zero setup.</p>
            </div>
            <span class="sm-section-note"><i class="ph ph-sparkle" aria-hidden="true"></i> Four platforms. One place.</span>
        </div>

        <div class="sm-filters" role="group" aria-label="Filter tools by platform">
            <button type="button" data-filter="all" class="is-active" aria-pressed="true">All platforms</button>
            @foreach($platformOrder as $platformSlug)
            <button type="button" data-filter="{{ $platformSlug }}" aria-pressed="false">{{ $platformNames[$platformSlug] }}</button>
            @endforeach
        </div>

        <div class="sm-tool-grid">
            @foreach($toolPicks as [$toolPlatform, $toolSlug])
                @php $toolItem = \App\Support\PlatformCatalog::tool($toolPlatform, $toolSlug); @endphp
                @if($toolItem)
                <a href="{{ route('viewer.tool', ['platform'=>$toolPlatform, 'tool'=>$toolSlug]) }}" class="sm-tool-card" data-tool-platform="{{ $toolPlatform }}" {{ $loop->index >= 6 ? 'hidden' : '' }}>
                    <div class="sm-tool-top">
                        <span class="sm-platform-icon sm-platform-{{ $toolPlatform }}">
                            <i class="ph {{ $platformIcons[$toolPlatform] }}" aria-hidden="true"></i>
                        </span>
                        <div class="sm-tool-heading">
                            <span class="sm-tool-platform-name">{{ $platformNames[$toolPlatform] }}</span>
                            <h3>{{ $toolItem['name'] }}</h3>
                        </div>
                    </div>
                    <p>{{ $toolItem['desc'] }}</p>
                    <span class="sm-tool-bottom">
                        <span class="sm-tool-type">{{ ($toolItem['intent'] ?? '') === 'download' ? 'Media downloader' : 'Public content viewer' }}</span>
                        <span class="sm-tool-action">Open tool</span>
                        <span class="sm-tool-arrow"><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></span>
                    </span>
                </a>
                @endif
            @endforeach
        </div>

        <div class="sm-tools-bottom flex-wrap">
            <p>Public content only. Availability depends on network visibility.</p>
            <button type="button" id="sm-show-tools">
                <span>View all tools</span>
                <i class="ph-bold ph-arrow-right" aria-hidden="true"></i>
            </button>
        </div>
    </section>
    </div>

    <!-- How It Works Section -->
    <div class="sm-section-band sm-band-how">
    <section class="sm-how sm-container" id="how-it-works" aria-labelledby="how-title">
        <div class="sm-how-intro">
            <p class="sm-kicker">Simple & Transparent</p>
            <h2 id="how-title">From link to your next find.</h2>
            <p>No complicated software or logins required. Follow three simple steps to start exploring.</p>
            <a href="#main-search-container" class="sm-how-cta">
                <span>Try it right now</span>
                <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i>
            </a>
        </div>
        <div class="sm-steps">
            <article class="sm-step-card">
                <span class="sm-step-number">STEP 01</span>
                <div>
                    <span class="sm-step-icon"><i class="ph-bold ph-link" aria-hidden="true"></i></span>
                    <h3>Bring a public link</h3>
                    <p>Copy any public profile URL, video link, or photo post from Facebook, X, Threads, or LinkedIn.</p>
                </div>
            </article>
            <article class="sm-step-card">
                <span class="sm-step-number">STEP 02</span>
                <div>
                    <span class="sm-step-icon"><i class="ph-bold ph-scan" aria-hidden="true"></i></span>
                    <h3>See what's out there</h3>
                    <p>Paste your link into smviewly, complete the quick security check, and safely preview available public content.</p>
                </div>
            </article>
            <article class="sm-step-card">
                <span class="sm-step-number">STEP 03</span>
                <div>
                    <span class="sm-step-icon"><i class="ph-bold ph-download-simple" aria-hidden="true"></i></span>
                    <h3>Keep the inspiration</h3>
                    <p>Download available media that you own or have permission to save. File quality depends on the source.</p>
                </div>
            </article>
        </div>
    </section>
    </div>

    <!-- Principles Banner -->
    <div class="sm-section-band sm-band-usecases">
    <section class="sm-container sm-usecases" aria-labelledby="usecases-title">
        <div class="sm-section-heading"><div><p class="sm-kicker">A few ways to use smviewly</p><h2 id="usecases-title">For the work behind your next idea.</h2><p class="sm-section-sub">A useful starting point for public content research, creative references, and company discovery.</p></div></div>
        <div class="sm-usecase-grid">
            <a href="{{ route('viewer.tool', ['platform' => 'facebook', 'tool' => 'page-posts']) }}" class="sm-usecase-card"><span class="sm-usecase-number">01 / RESEARCH</span><i class="ph ph-binoculars" aria-hidden="true"></i><h3>See what brands are sharing.</h3><p>Explore public Facebook page posts to follow announcements, campaigns, and conversations.</p><span class="sm-usecase-link">Explore page posts <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
            <a href="{{ route('viewer.tool', ['platform' => 'twitter', 'tool' => 'media-viewer']) }}" class="sm-usecase-card"><span class="sm-usecase-number">02 / INSPIRATION</span><i class="ph ph-lightbulb" aria-hidden="true"></i><h3>Keep your references<br class="sm-reference-break"> together.</h3><p>Browse available public X media for creative references. Save only files you have permission to use.</p><span class="sm-usecase-link">Discover public media <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
            <a href="{{ route('viewer.tool', ['platform' => 'linkedin', 'tool' => 'company-viewer']) }}" class="sm-usecase-card"><span class="sm-usecase-number">03 / DISCOVERY</span><i class="ph ph-buildings" aria-hidden="true"></i><h3>Get to know a company.</h3><p>Look up publicly available LinkedIn company information before your next research session.</p><span class="sm-usecase-link">View company profiles <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
    </section>
    </div>

    <div class="sm-section-band sm-band-principles">
    <section class="sm-principles sm-container">
        <div class="sm-principles-icon">
            <i class="ph-bold ph-shield-check" aria-hidden="true"></i>
        </div>
        <div class="sm-principles-content">
            <h2>Public means public.</h2>
            <p>We help you explore publicly available media responsibly. Private profiles, restricted feeds, and password-protected accounts remain completely private.</p>
        </div>
        <a href="{{ route('privacy') }}" class="sm-principles-link">
            <span>Our Privacy Approach</span>
            <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i>
        </a>
    </section>
    </div>

    <!-- Blog & Guides Section -->
    @if($posts->isNotEmpty())
    <div class="sm-section-band sm-band-guides">
    <section class="sm-guides sm-container" id="guides" aria-labelledby="guides-title">
        <div class="sm-section-heading">
            <div>
                <p class="sm-kicker">From the journal</p>
                <h2 id="guides-title">Practical guides, when you need them.</h2>
                <p class="sm-section-sub">Expert insights, digital privacy tips, and media archiving best practices.</p>
            </div>
            <a class="sm-text-link" href="{{ route('blog.index') }}">
                <span>View all articles</span>
                <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i>
            </a>
        </div>
        <div class="sm-guide-grid">
            @foreach($posts as $post)
            <a class="sm-guide-card" href="{{ route('blog.show', $post->slug) }}">
                <div class="sm-guide-image">
                    <img src="{{ $post->image_url }}" alt="{{ $post->title }}" loading="lazy" width="600" height="400">
                    <span class="sm-guide-badge"><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></span>
                </div>
                <div class="sm-guide-meta">
                    <span class="sm-guide-category">{{ $post->category?->name ?? 'Social Media Guide' }}</span>
                    <span class="sm-guide-date">{{ $post->created_at ? $post->created_at->format('M d, Y') : 'Featured' }}</span>
                </div>
                <h3>{{ $post->title }}</h3>
                <p>{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 125) }}</p>
                <span class="sm-guide-readmore">
                    <span>Read Guide</span>
                    <i class="ph-bold ph-arrow-right" aria-hidden="true"></i>
                </span>
            </a>
            @endforeach
        </div>
    </section>
    </div>
    @endif

    <!-- FAQ Section with Smooth Exclusive Accordion -->
    <div class="sm-section-band sm-band-faq">
    <section class="sm-faq sm-container" id="faqs" aria-labelledby="faq-title">
        <div class="sm-faq-sidebar">
            <p class="sm-kicker">Common Questions</p>
            <h2 id="faq-title">A few things worth knowing.</h2>
            <p class="sm-faq-sub">Find quick answers to common questions about using smviewly.</p>
            <a class="sm-text-link" href="{{ route('contact') }}">
                <span>Still have questions? Contact support</span>
                <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i>
            </a>
        </div>
        <div class="sm-faq-list" id="sm-faq-accordion">
            @php
                $questions = [
                    ['Do I need an account or social login to get started?', 'No account or social media credentials are ever needed. Simply choose a platform, paste any public link or username, complete the quick security check, and view available content.'],
                    ['Can I view private profiles or restricted posts?', 'No. smviewly strictly respects platform privacy policies. We only fetch publicly accessible content. Private profiles, closed groups, and subscriber-only media cannot be viewed.'],
                    ['Which social media platforms are currently supported?', 'We currently support Facebook, X (formerly Twitter), Threads, and LinkedIn. Available tools vary by platform: Facebook pages and posts, X profiles and media, Threads posts, and LinkedIn companies and profiles.'],
                    ['Can I download original quality videos and photos?', 'You can save photos and videos when the source provides a downloadable file. Resolution and format depend on the original upload and platform availability. Only download media you own or have permission to save.'],
                    ['Why might a specific link not return results?', 'The content may be set to private, geo-restricted, removed by the creator, or temporarily rate-limited by the platform server. Double-check your URL and try again.'],
                ];
            @endphp
            @foreach($questions as $index => [$question, $answer])
            <div class="sm-faq-item {{ $index === 0 ? 'is-open' : '' }}">
                <button type="button" class="sm-faq-question" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                    <span>{{ $question }}</span>
                    <span class="sm-faq-toggle">
                        <i class="ph-bold ph-plus" aria-hidden="true"></i>
                    </span>
                </button>
                <div class="sm-faq-collapse">
                    <div class="sm-faq-body">
                        <p>{{ $answer }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    </div>

    <!-- CTA Section -->
    <div class="sm-section-band sm-band-cta">
    <section class="sm-cta sm-container">
        <div class="sm-cta-text">
            <span class="sm-cta-label"><i class="ph-bold ph-sparkle" aria-hidden="true"></i> Public profiles. Useful tools. No social login.</span>
            <h2>Have a link in mind?<br>Take a closer look.</h2>
            <p class="sm-cta-description">Explore public pages, profiles, posts, and available media from Facebook, X, Threads, and LinkedIn.</p>
        </div>
        <div class="sm-cta-action">
            <a class="sm-cta-btn" href="#main-search-container">
                <span>Start exploring</span>
                <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i>
            </a>
            <p class="sm-cta-note">No downloads required • Instant web access</p>
        </div>
        <span class="sm-cta-art" aria-hidden="true">*</span>
    </section>
    </div>
</div>
@endsection



@push('scripts')
@include('partials.live-search-script')
<script>
(() => {
    const platformInput = document.getElementById('active-platform-input');
    const toolInput = document.getElementById('active-tool-input');
    const searchInput = document.getElementById('search-input');
    const defaults = @json($platformMeta);
    const names = @json($platformNames);
    const platformButtons = document.querySelectorAll('[data-platform]');
    
    function choosePlatform(slug) {
        if (!defaults[slug]) return;
        platformInput.value = slug;
        toolInput.value = defaults[slug].default_tool;
        searchInput.placeholder = 'Paste a ' + names[slug] + ' link or enter a username';
        platformButtons.forEach(button => {
            const selected = button.dataset.platform === slug;
            button.classList.toggle('is-active', selected);
            button.setAttribute('aria-pressed', String(selected));
        });
    }
    
    platformButtons.forEach(button => button.addEventListener('click', () => choosePlatform(button.dataset.platform)));
    choosePlatform(@json(old('platform', 'facebook')));
    
    function detectPlatform() {
        try {
            const host = new URL(searchInput.value.trim()).hostname.toLowerCase();
            const matches = domain => host === domain || host.endsWith('.' + domain);
            if (matches('facebook.com') || matches('fb.watch')) choosePlatform('facebook');
            else if (matches('x.com') || matches('twitter.com')) choosePlatform('twitter');
            else if (matches('threads.net') || matches('threads.com')) choosePlatform('threads');
            else if (matches('linkedin.com')) choosePlatform('linkedin');
        } catch (_) { /* Usernames do not have a URL hostname. */ }
    }
    
    searchInput.addEventListener('input', detectPlatform);
    
    const pasteBtn = document.getElementById('sm-paste');
    if (pasteBtn) {
        pasteBtn.addEventListener('click', async () => {
            try { 
                searchInput.value = (await navigator.clipboard.readText()).trim(); 
                detectPlatform(); 
            } catch (_) {
                const errorBox = document.getElementById('captcha-error-container');
                const errorText = document.getElementById('captcha-error-text');
                if (errorBox && errorText) {
                    errorText.textContent = 'Please paste your link directly into the field using your device paste command.';
                    errorBox.classList.remove('hidden');
                }
            }
            searchInput.focus();
        });
    }
    
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const filters = document.querySelectorAll('[data-filter]');
    const cards = Array.from(document.querySelectorAll('[data-tool-platform]'));
    const expandButton = document.getElementById('sm-show-tools');
    let activeFilter = 'all';
    let expanded = false;
    
    function updateTools() {
        cards.forEach((card, index) => { 
            card.hidden = activeFilter === 'all' ? (!expanded && index >= 6) : card.dataset.toolPlatform !== activeFilter; 
        });
        filters.forEach(button => { 
            const selected = button.dataset.filter === activeFilter; 
            button.classList.toggle('is-active', selected); 
            button.setAttribute('aria-pressed', String(selected)); 
        });
        if (expandButton) {
            expandButton.hidden = activeFilter !== 'all' || expanded;
        }
    }
    
    filters.forEach(button => button.addEventListener('click', () => { 
        activeFilter = button.dataset.filter; 
        expanded = false; 
        updateTools(); 
    }));
    
    if (expandButton) {
        expandButton.addEventListener('click', () => { 
            expanded = true; 
            updateTools(); 
        });
    }

    // Global Smooth Scroll for Hash Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (!targetId || targetId === '#' || this.classList.contains('sm-back-to-top')) return;
            const targetEl = document.querySelector(targetId);
            if (targetEl) {
                e.preventDefault();
                const header = document.querySelector('.sm-header');
                const headerHeight = header ? header.offsetHeight : 84;
                const targetPosition = targetEl.getBoundingClientRect().top + window.pageYOffset - (headerHeight + 20);
                window.scrollTo({
                    top: targetPosition,
                    behavior: reducedMotion.matches ? 'auto' : 'smooth'
                });
            }
        });
    });
})();
</script>
@endpush

@push('ld-json')
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => 'Social media tools by platform', 'itemListElement' => collect($platformOrder)->map(fn ($slug, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $platformNames[$slug].' tools', 'url' => route('viewer.tool', ['platform' => $slug])])->all()], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
