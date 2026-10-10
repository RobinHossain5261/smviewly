@php
    $platformOrder = \App\Support\PlatformCatalog::slugs();
    $platformIcons = collect(\App\Support\PlatformCatalog::all())->map(fn ($meta) => $meta['icon'])->all();
    $platformNames = collect(\App\Support\PlatformCatalog::all())->map(fn ($meta) => $meta['name'])->all();
@endphp
<header class="sm-header" id="page-top">
    <div class="sm-container sm-nav">
        <a class="sm-brand" href="{{ route('home') }}" aria-label="{{ $siteSettings->brand_name }} home">
            <span class="sm-logo"><i class="ph-bold ph-scan" aria-hidden="true"></i></span>
            <span>{{ $siteSettings->brand_name }}</span><span class="sm-brand-dot">.</span>
        </a>
        <nav class="sm-desktop-nav" aria-label="Main navigation">
            <a class="{{ request()->routeIs('home') ? 'is-current' : '' }}" href="{{ route('home') }}" {{ request()->routeIs('home') ? 'aria-current=page' : '' }}>Home</a>
            <details class="sm-nav-tools {{ request()->routeIs('viewer.*', 'tools') ? 'is-current' : '' }}">
                <summary>Tools <i class="ph ph-caret-down" aria-hidden="true"></i></summary>
                <div class="sm-tools-dropdown">
                    @foreach($platformOrder as $platformSlug)
                    <a href="{{ route('viewer.tool', ['platform' => $platformSlug]) }}" class="{{ request()->route('platform') === $platformSlug ? 'is-current' : '' }}" @if(request()->routeIs('viewer.tool') && request()->route('platform') === $platformSlug && !request()->route('tool')) aria-current="page" @endif>
                        <span class="sm-dropdown-icon"><i class="ph {{ $platformIcons[$platformSlug] }}" aria-hidden="true"></i></span>
                        <span><strong>{{ $platformNames[$platformSlug] }} Tools</strong><small>{{ \App\Support\PlatformCatalog::get($platformSlug)['short'] }}</small></span>
                        <i class="ph ph-arrow-up-right" aria-hidden="true"></i>
                    </a>
                    @endforeach
                </div>
            </details>
            <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'is-current' : '' }}" {{ request()->routeIs('blog.*') ? 'aria-current=page' : '' }}>Blog</a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'is-current' : '' }}" {{ request()->routeIs('about') ? 'aria-current=page' : '' }}>About</a>
            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'is-current' : '' }}" {{ request()->routeIs('contact') ? 'aria-current=page' : '' }}>Contact</a>
        </nav>
        <div class="sm-nav-auth">
            @auth
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('home') }}">{{ auth()->user()->isAdmin() ? 'Dashboard' : auth()->user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="sm-signup">Sign Out</button></form>
            @else
            <a href="{{ route('signin') }}">Sign In</a><a class="sm-signup" href="{{ route('signup') }}">Sign Up</a>
            @endauth
        </div>
        <button class="sm-menu" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="sm-mobile-nav"><i class="ph ph-list" aria-hidden="true"></i></button>
    </div>
    <dialog class="sm-drawer" id="sm-mobile-drawer" aria-label="Main navigation">
    <button type="button" class="sm-drawer-overlay" aria-label="Close navigation" tabindex="-1"></button>
    <nav id="sm-mobile-nav" aria-label="Mobile navigation">
        <div class="sm-drawer-heading"><strong>{{ $siteSettings->brand_name }}</strong><button type="button" class="sm-drawer-close" aria-label="Close navigation"><i class="ph ph-x" aria-hidden="true"></i></button></div>
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-current' : '' }}" {{ request()->routeIs('home') ? 'aria-current=page' : '' }}>Home</a>
        <div class="sm-mobile-tools"><button type="button" class="sm-mobile-tools-toggle" aria-expanded="false" aria-controls="sm-mobile-tool-links">Tools <i class="ph ph-caret-down" aria-hidden="true"></i></button><div class="sm-mobile-tools-collapse" id="sm-mobile-tool-links" inert><div>
            @foreach($platformOrder as $platformSlug)
            <a href="{{ route('viewer.tool', ['platform' => $platformSlug]) }}" class="{{ request()->route('platform') === $platformSlug ? 'is-current' : '' }}" @if(request()->routeIs('viewer.tool') && request()->route('platform') === $platformSlug && !request()->route('tool')) aria-current="page" @endif><i class="ph {{ $platformIcons[$platformSlug] }}" aria-hidden="true"></i> {{ $platformNames[$platformSlug] }} Tools</a>
            @endforeach
        </div></div></div>
        <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'is-current' : '' }}" {{ request()->routeIs('blog.*') ? 'aria-current=page' : '' }}>Blog</a><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'is-current' : '' }}" {{ request()->routeIs('about') ? 'aria-current=page' : '' }}>About</a><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'is-current' : '' }}" {{ request()->routeIs('contact') ? 'aria-current=page' : '' }}>Contact</a>
            @auth
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('home') }}">{{ auth()->user()->isAdmin() ? 'Dashboard' : auth()->user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="sm-mobile-cta">Sign Out</button></form>
            @else
            <a href="{{ route('signin') }}">Sign In</a><a class="sm-mobile-cta" href="{{ route('signup') }}">Sign Up</a>
            @endauth
    </nav>
    </dialog>
</header>
