@extends('layouts.app')
@section('title', $meta['name'].' Tools — Public Profiles & Media | '.$siteSettings->brand_name)
@section('description', 'Explore '.$meta['name'].' tools for '.$meta['short'].'. Browse available public content without connecting your social account.')
@section('canonical', route('viewer.tool', ['platform' => $platform]))
@section('content')
<div class="sm-home sm-platform-page">
    <section class="sm-hub-hero">
        <div class="sm-container">
            <nav class="sm-hub-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><i class="ph ph-caret-right" aria-hidden="true"></i><span>{{ $meta['name'] }} tools</span></nav>
            <div class="sm-hub-banner-grid"><div class="sm-hub-banner-copy">
            <p class="sm-kicker">{{ count($tools) }} tools. One place to start.</p>
            <h1>{{ $meta['name'] }} tools.<br><em>Find what you need.</em></h1>
            <p class="sm-hub-description">Explore {{ strtolower($meta['short']) }} with tools built for public content. Choose what you want to view, then enter a username or link.</p>
            <a class="sm-button sm-hub-start" href="{{ route('viewer.tool', ['platform' => $platform, 'tool' => \App\Support\PlatformCatalog::defaultTool($platform)['slug']]) }}">Start exploring <i class="ph ph-arrow-right" aria-hidden="true"></i></a>
            <div class="sm-hero-benefits"><span><i class="ph ph-user-circle" aria-hidden="true"></i> No social login</span><span><i class="ph ph-shield-check" aria-hidden="true"></i> Public content only</span></div>
            </div>
            <aside class="sm-hub-preview" aria-label="Featured {{ $meta['name'] }} tools">
                <div class="sm-hub-preview-heading"><span><i class="ph {{ $meta['icon'] }}" aria-hidden="true"></i> {{ $meta['name'] }}</span><small>Choose your next lookup</small></div>
                @foreach(collect($tools)->take(3) as $previewTool)
                @php $previewMeta = \App\Support\PlatformCatalog::tool($platform, $previewTool['slug']); @endphp
                <a class="sm-hub-preview-row" href="{{ route('viewer.tool', ['platform' => $platform, 'tool' => $previewTool['slug']]) }}"><span class="sm-platform-icon"><i class="ph {{ $previewTool['icon'] }}" aria-hidden="true"></i></span><span><strong>{{ $previewMeta['name'] }}</strong><small>{{ $previewTool['desc'] }}</small></span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a>
                @endforeach
                <p><i class="ph ph-shield-check" aria-hidden="true"></i> {{ count($tools) }} tools for publicly available content</p>
            </aside></div>
        </div>
    </section>
    <div class="sm-section-band">
        <section class="sm-container" id="grid-section" aria-labelledby="hub-tools-title">
            <div class="sm-section-heading"><div><p class="sm-kicker">The toolkit</p><h2 id="hub-tools-title">Find the {{ $meta['name'] }} tool you need.</h2><p class="sm-section-sub">Each tool has its own input guidance and preview. Results depend on what the source makes publicly available.</p></div></div>
            <div class="sm-tool-grid">
                @foreach($tools as $toolItem)
                @php $toolMeta = \App\Support\PlatformCatalog::tool($platform, $toolItem['slug']); @endphp
                <a class="sm-tool-card" href="{{ route('viewer.tool', ['platform' => $platform, 'tool' => $toolItem['slug']]) }}" data-tool-platform="{{ $platform }}">
                    <div class="sm-tool-top"><span class="sm-platform-icon"><i class="ph {{ $toolItem['icon'] }}" aria-hidden="true"></i></span><div class="sm-tool-heading"><span class="sm-tool-platform-name">{{ $meta['name'] }}</span><h3>{{ $toolMeta['name'] }}</h3></div></div>
                    <p>{{ $toolItem['desc'] }}</p>
                    <span class="sm-tool-bottom"><span class="sm-tool-type">{{ $toolMeta['intent'] === 'download' ? 'Media downloader' : ($toolMeta['intent'] === 'search' ? 'Public search' : 'Public content viewer') }}</span><span class="sm-tool-action">Open tool</span><span class="sm-tool-arrow"><i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></span>
                </a>
                @endforeach
            </div>
        </section>
    </div>
    @include('partials.platform-sections')

</div>
@endsection

@push('ld-json')
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')], ['@type' => 'ListItem', 'position' => 2, 'name' => $meta['name'].' tools', 'item' => route('viewer.tool', ['platform' => $platform])]]], JSON_HEX_TAG|JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
