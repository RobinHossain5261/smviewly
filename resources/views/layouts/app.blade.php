<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="--primary-color: {{ $siteSettings->primary_color }}; --secondary-color: {{ $siteSettings->secondary_color }};">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $siteSettings->meta_title)</title>
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large')">
    <meta name="description" content="@yield('description', $siteSettings->meta_description)">
    <meta name="keywords" content="@yield('keywords', 'facebook profile viewer, facebook video downloader, x twitter profile viewer, tweet viewer, twitter media downloader, threads profile viewer, linkedin profile lookup, incognito social media downloader, free social media viewer')">
    @php
        $ogTitle = html_entity_decode(trim($__env->yieldContent('title', $siteSettings->meta_title)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $ogDescription = html_entity_decode(trim($__env->yieldContent('description', $siteSettings->meta_description)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $ogImage = !empty($siteSettings->logo_path)
            ? asset('storage/'.$siteSettings->logo_path)
            : url('/android-chrome-512x512.png');
        $brandExpand = $siteSettings->brand_expand ?? 'Social Media Viewer';
        $path = request()->getPathInfo();
        $canonicalDefault = rtrim(config('app.url'), '/').($path === '/' ? '' : $path);
        $sameAs = array_values(array_filter([
            $siteSettings->linkedin_url ?? null,
            $siteSettings->twitter_url ?? null,
            $siteSettings->facebook_url ?? null,
        ]));
        $organization = [
            '@type' => 'Organization',
            '@id' => url('/').'/#organization',
            'name' => $siteSettings->brand_name,
            'alternateName' => $brandExpand,
            'url' => url('/'),
            'email' => $siteSettings->contact_email,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $ogImage,
                'width' => 512,
                'height' => 512,
            ],
            'description' => $siteSettings->meta_description,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => $siteSettings->contact_email,
                'url' => url('/contact'),
                'availableLanguage' => ['English'],
            ],
        ];
        if (! empty($siteSettings->contact_phone)) {
            $organization['telephone'] = $siteSettings->contact_phone;
            $organization['contactPoint']['telephone'] = $siteSettings->contact_phone;
        }
        if ($sameAs) {
            $organization['sameAs'] = $sameAs;
        }

        $rawLogo = $siteSettings->logo_path ?? 'images/logo.png';
        $logoUrl = (str_starts_with($rawLogo, 'images/') || str_starts_with($rawLogo, 'http') || str_starts_with($rawLogo, '/'))
            ? asset($rawLogo)
            : asset('storage/' . $rawLogo);

        $rawFav = $siteSettings->favicon_path ?? 'images/favicon.ico';
        $faviconUrl = (str_starts_with($rawFav, 'images/') || str_starts_with($rawFav, 'http') || str_starts_with($rawFav, '/'))
            ? asset($rawFav)
            : asset('storage/' . $rawFav);
    @endphp
    <link rel="canonical" href="@yield('canonical', $canonicalDefault)">
    <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ $faviconUrl }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ $faviconUrl }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $faviconUrl }}">
    <link rel="manifest" href="{{ url('/site.webmanifest') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteSettings->brand_name }}">
    <meta property="og:locale" content="en_US">
    <meta property="og:url" content="@yield('canonical', $canonicalDefault)">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            $organization,
            [
                '@type' => 'WebSite',
                '@id' => url('/').'/#website',
                'name' => $siteSettings->brand_name,
                'alternateName' => $brandExpand,
                'url' => url('/'),
                'publisher' => ['@id' => url('/').'/#organization'],
                'inLanguage' => 'en',
            ],
            [
                '@type' => 'SoftwareApplication',
                'name' => $siteSettings->brand_name,
                'alternateName' => $brandExpand,
                'applicationCategory' => 'UtilitiesApplication',
                'operatingSystem' => 'Web',
                'url' => url('/'),
                'description' => $siteSettings->meta_description,
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'USD',
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
    </script>
    @stack('ld-json')

    <!-- Fonts -->
    <link rel="preload" href="{{ asset('fonts/jakarta/plus-jakarta-sans-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>

    <!-- Phosphor Icons (Premium Modern Icons Library) -->
    <link rel="stylesheet" href="{{ asset('fonts/icons/regular.css') }}">
    <link rel="stylesheet" href="{{ asset('fonts/icons/bold.css') }}">
    <link rel="stylesheet" href="{{ asset('fonts/icons/fill.css') }}">

    <!-- Swiper CSS (CDN) -->
    @unless(request()->routeIs('home'))
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    
    <!-- Swiper JS (CDN) -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    @endunless

    <!-- CSS / Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dynamic Custom Head Scripts -->
    @if($siteSettings->custom_head_scripts)
        {!! $siteSettings->custom_head_scripts !!}
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Grid Line Background Utility */
        .bg-grid-line {
            background-size: 32px 32px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0.02) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
        }
        /* Remove Shadows globally if requested */
        .shadow, .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl, .shadow-2xl {
            box-shadow: none !important;
        }
        /* Custom hover styling with primary color transparency */
        .card-hover-primary {
            transition: all 0.2s ease-in-out;
        }
        .card-hover-primary:hover {
            background-color: var(--primary-color-opacity, rgba(37, 99, 235, 0.04)) !important;
            border-color: var(--primary-color, rgba(37, 99, 235, 0.3)) !important;
        }
        /* Pointer cursor forces */
        button, a, select, input[type="submit"], input[type="button"], .cursor-pointer {
            cursor: pointer !important;
        }
    </style>
    @vite(['resources/css/home.css', 'resources/css/home-modern.css'])
    @stack('styles')
</head>
<body class="bg-white text-slate-800 antialiased flex flex-col min-h-screen selection:bg-brand-primary/20 selection:text-brand-primary">

    <!-- Header Navbar -->
    @if(request()->routeIs('signin', 'signup'))
        <header class="sm-account-header"><div class="sm-container"><a class="sm-brand" href="{{ route('home') }}"><span class="sm-logo"><i class="ph ph-scan" aria-hidden="true"></i></span><span>{{ $siteSettings->brand_name }}</span></a><a class="sm-account-back" href="{{ route('home') }}"><i class="ph ph-arrow-left" aria-hidden="true"></i> Back to site</a></div></header>
    @else
        @include('partials.site-header')
    @endif
    <!-- Main Content Area -->
    <main class="flex-grow flex flex-col justify-start {{ request()->routeIs('blog.show', 'privacy', 'terms') ? 'sm-article-shell' : 'overflow-x-hidden' }}">
        @yield('content')
    </main>

    <!-- Footer Section (Light Background for Crystal Clear Logo Visibility) -->
    @if(request()->routeIs('signin', 'signup'))
        <footer class="sm-account-footer"><div class="sm-container"><span>&copy; {{ date('Y') }} {{ $siteSettings->brand_name }}</span><nav aria-label="Account help"><a href="{{ route('privacy') }}">Privacy</a><a href="{{ route('terms') }}">Terms</a><a href="{{ route('contact') }}">Support</a></nav></div></footer>
    @else
        @include('partials.site-footer')
    @endif


    <!-- Sidebar Animation Script Toggle -->
    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mobile-sidebar');
            const panel = document.getElementById('mobile-sidebar-panel');
            if (sidebar.classList.contains('pointer-events-none')) {
                sidebar.classList.remove('pointer-events-none', 'opacity-0');
                sidebar.classList.add('opacity-100');
                panel.classList.remove('translate-x-full');
                panel.classList.add('translate-x-0');
            } else {
                sidebar.classList.remove('opacity-100');
                sidebar.classList.add('opacity-0', 'pointer-events-none');
                panel.classList.remove('translate-x-0');
                panel.classList.add('translate-x-full');
            }
        }
    </script>
    <!-- Media lightbox (photo / video posts) -->
    <div id="viewer-media-modal" class="hidden fixed inset-0 z-[80] bg-black/80 flex items-center justify-center p-4" onclick="closeViewerMedia()">
        <div class="max-w-4xl w-full flex flex-col items-center gap-4" onclick="event.stopPropagation()">
            <div id="viewer-media-image-wrap" class="relative hidden">
                <img id="viewer-media-image" alt="" class="max-h-[80vh] w-auto max-w-full rounded-xl object-contain">
                <button type="button" class="absolute -top-3 -right-3 flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-900 shadow-lg hover:bg-slate-100" onclick="closeViewerMedia(); event.stopPropagation();" aria-label="Close">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
            <div id="viewer-media-video-wrap" class="relative w-full hidden">
                <video id="viewer-media-video" controls playsinline class="w-full max-h-[80vh] bg-black rounded-xl"></video>
                <button type="button" class="absolute -top-3 -right-3 flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-900 shadow-lg hover:bg-slate-100" onclick="closeViewerMedia(); event.stopPropagation();" aria-label="Close">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
            <div id="viewer-media-fallback-wrap" class="relative hidden">
                <a id="viewer-media-fallback" href="#" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-white px-4 py-2 text-sm font-extrabold text-slate-900">Open on source</a>
                <button type="button" class="absolute -top-3 -right-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-900 shadow-lg hover:bg-slate-100" onclick="closeViewerMedia(); event.stopPropagation();" aria-label="Close">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        window.openViewerMedia = function (el) {
            const modal = document.getElementById('viewer-media-modal');
            const video = document.getElementById('viewer-media-video');
            const videoWrap = document.getElementById('viewer-media-video-wrap');
            const image = document.getElementById('viewer-media-image');
            const imageWrap = document.getElementById('viewer-media-image-wrap');
            const fallback = document.getElementById('viewer-media-fallback');
            const fallbackWrap = document.getElementById('viewer-media-fallback-wrap');
            if (!modal) return;

            const isVideo = el.dataset.video === '1';
            const url = el.dataset.mediaUrl || '';
            const poster = el.dataset.poster || '';
            const permalink = el.dataset.permalink || '';
            const proxied = (src) => src ? ('{{ url("/media") }}?url=' + encodeURIComponent(src)) : '';

            if (videoWrap) videoWrap.classList.add('hidden');
            if (imageWrap) imageWrap.classList.add('hidden');
            if (fallbackWrap) fallbackWrap.classList.add('hidden');
            video.pause();
            video.removeAttribute('src');
            video.load();

            if (isVideo && url) {
                if (poster) video.setAttribute('poster', proxied(poster));
                video.src = proxied(url);
                if (videoWrap) videoWrap.classList.remove('hidden');
                video.onerror = () => {
                    if (permalink) {
                        fallback.href = permalink;
                        fallback.textContent = 'Open video on source';
                        if (videoWrap) videoWrap.classList.add('hidden');
                        if (fallbackWrap) fallbackWrap.classList.remove('hidden');
                    }
                };
                video.play().catch(() => {});
            } else if (!isVideo && (url || poster)) {
                image.src = proxied(url || poster);
                if (imageWrap) imageWrap.classList.remove('hidden');
            } else if (permalink) {
                fallback.href = permalink;
                if (fallbackWrap) fallbackWrap.classList.remove('hidden');
            } else {
                return;
            }

            modal.classList.remove('hidden');
        };

        window.closeViewerMedia = function () {
            const modal = document.getElementById('viewer-media-modal');
            const video = document.getElementById('viewer-media-video');
            if (video) {
                video.pause();
                video.removeAttribute('src');
                video.load();
            }
            if (modal) modal.classList.add('hidden');
        };

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') window.closeViewerMedia();
        });
    </script>
    @stack('scripts')
</body>
</html>
