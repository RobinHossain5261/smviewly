@extends('layouts.app')
@section('title', '404 - Page Not Found | '.$siteSettings->brand_name)
@section('description', 'This page could not be found. Explore our public social media tools or return to the home page.')
@section('robots', 'noindex, follow')
@push('styles')
    @include('errors.404-styles')
@endpush
@section('content')
<div class="sm-home sm-error-page">
    <section class="sm-error-hero" aria-labelledby="error-title">
        <div class="sm-container sm-error-layout">
            <div class="sm-error-copy">
                <p class="sm-kicker">ERROR 404 · PAGE NOT FOUND</p>
                <h1 id="error-title">A little lost?<br><em>Let’s get you back.</em></h1>
                <p class="sm-error-description">We couldn’t find the page you’re looking for. The link may have changed, or the address may have a typo.</p>
                <div class="sm-error-actions">
                    <a class="sm-error-primary" href="{{ route('home') }}"><i class="ph ph-house" aria-hidden="true"></i> Back to home <i class="ph ph-arrow-right" aria-hidden="true"></i></a>
                    <a class="sm-error-secondary" href="{{ route('tools') }}">Explore tools <i class="ph ph-squares-four" aria-hidden="true"></i></a>
                </div>
                <p class="sm-error-help">Think something’s broken? <a href="{{ route('contact') }}">Let us know <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></p>
            </div>
            <div class="sm-error-art" aria-hidden="true">
                <span class="sm-error-orbit"></span>
                <div class="sm-error-window">
                    <div class="sm-error-window-bar"><span></span><span></span><span></span><i class="ph ph-link-break"></i></div>
                    <div class="sm-error-number">404</div>
                    <div class="sm-error-window-label"><i class="ph ph-magnifying-glass"></i> Page out of sight</div>
                </div>
                <span class="sm-error-note"><i class="ph ph-compass"></i> A fresh start is one click away</span>
            </div>
        </div>
    </section>
    <section class="sm-container sm-error-discover" aria-labelledby="error-discover-title">
        <div class="sm-error-discover-heading"><div><p class="sm-kicker">FIND YOUR NEXT STOP</p><h2 id="error-discover-title">There’s more to explore.</h2></div><a href="{{ route('blog.index') }}">Read our guides <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div>
        <nav class="sm-error-links" aria-label="Popular platform tools">
            @foreach(\App\Support\PlatformCatalog::all() as $slug => $network)
                <a href="{{ route('viewer.tool', ['platform' => $slug]) }}"><span class="sm-platform-icon"><i class="ph {{ $network['icon'] }}" aria-hidden="true"></i></span><span><strong>{{ $network['name'] }}</strong><small>Explore public content</small></span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a>
            @endforeach
        </nav>
    </section>
</div>
@endsection
