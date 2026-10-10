<div class="sm-home sm-legal-page">
    <header class="sm-hub-hero"><div class="sm-container"><nav class="sm-hub-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><i class="ph ph-caret-right" aria-hidden="true"></i><span>{{ $pageTitle }}</span></nav><p class="sm-kicker">Clear information</p><h1>{{ $pageTitle }}</h1><p class="sm-hub-description">{{ $pageIntro }}</p></div></header>
    <div class="sm-section-band"><div class="sm-container sm-legal-layout">
        <article class="sm-legal-copy">
            @foreach($sections as $index => [$heading, $paragraphs])
            <section id="policy-section-{{ $index + 1 }}"><h2><span>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>{{ $heading }}</h2>@foreach($paragraphs as $paragraph)<p>{{ $paragraph }}</p>@endforeach</section>
            @endforeach
            <div class="sm-legal-contact"><h2>Questions about this page?</h2><p>Contact {{ $siteSettings->brand_name }} if you need clarification.</p><a class="sm-principles-link" href="{{ route('contact') }}">Contact support <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div>
        </article>
        <aside class="sm-legal-sidebar"><div class="sm-sidebar-card"><h2>On this page</h2><nav aria-label="Page contents">@foreach($sections as $index => [$heading])<a href="#policy-section-{{ $index + 1 }}">{{ $heading }}</a>@endforeach</nav></div><nav class="sm-sidebar-card sm-legal-links" aria-label="Policies and help"><a href="{{ route('privacy') }}">Privacy Policy</a><a href="{{ route('terms') }}">Terms of Service</a><a href="{{ route('faqs') }}">Frequently Asked Questions</a></nav></aside>
    </div></div>
</div>
