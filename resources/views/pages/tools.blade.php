@extends('layouts.app')
@section('title', 'All Social Media Tools | '.$siteSettings->brand_name)
@section('description', 'Browse all available Facebook, X, Threads and LinkedIn tools in the smviewly toolkit.')
@section('canonical', route('tools'))
@section('content')
<div class="sm-home"><header class="sm-hub-hero"><div class="sm-container"><p class="sm-kicker">The complete toolkit</p><h1>Choose your platform.<br><em>Find the right tool.</em></h1><p class="sm-hub-description">Browse available public-content tools by network. Each tool includes its own input guidance.</p></div></header>
@foreach(\App\Support\PlatformCatalog::all() as $platformSlug => $platformInfo)
<div class="sm-section-band"><section class="sm-container" aria-labelledby="tools-{{ $platformSlug }}"><div class="sm-section-heading"><div><p class="sm-kicker">{{ count($platformInfo['tools']) }} tools</p><h2 id="tools-{{ $platformSlug }}">{{ $platformInfo['name'] }}</h2><p class="sm-section-sub">{{ $platformInfo['short'] }}</p></div><a class="sm-text-link" href="{{ route('viewer.tool', ['platform' => $platformSlug]) }}">Platform overview <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div><div class="sm-tool-grid">
@foreach($platformInfo['tools'] as $item)
@php $tool = \App\Support\PlatformCatalog::tool($platformSlug, $item['slug']); @endphp
<a class="sm-tool-card" href="{{ route('viewer.tool', ['platform' => $platformSlug, 'tool' => $item['slug']]) }}" data-tool-platform="{{ $platformSlug }}"><div class="sm-tool-top"><span class="sm-platform-icon"><i class="ph {{ $item['icon'] }}" aria-hidden="true"></i></span><div class="sm-tool-heading"><span class="sm-tool-platform-name">{{ $platformInfo['name'] }}</span><h3>{{ $tool['name'] }}</h3></div></div><p>{{ $item['desc'] }}</p><span class="sm-tool-bottom"><span class="sm-tool-action">Open tool</span><span class="sm-tool-arrow"><i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></span></a>
@endforeach
</div></section></div>
@endforeach
</div>
@endsection
