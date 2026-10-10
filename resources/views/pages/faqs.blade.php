@extends('layouts.app')
@section('title', 'Frequently Asked Questions | '.$siteSettings->brand_name)
@section('description', 'Answers about public social media lookups, supported tools, downloads and troubleshooting on '.$siteSettings->brand_name.'.')
@section('canonical', route('faqs'))
@section('content')
<div class="sm-home sm-faq-page">
    <header class="sm-hub-hero"><div class="sm-container"><nav class="sm-hub-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><i class="ph ph-caret-right" aria-hidden="true"></i><span>FAQs</span></nav><div class="sm-faq-banner-grid"><div><p class="sm-kicker">A little guidance</p><h1>Questions?<br><em>Start here.</em></h1><p class="sm-hub-description">Public content, supported inputs, downloads and the things that can affect a lookup.</p></div><aside class="sm-faq-banner-links" aria-label="Useful places to start"><p class="sm-kicker">Find your next step</p><a href="{{ route('tools') }}"><i class="ph ph-squares-four" aria-hidden="true"></i><span><strong>Choose a tool</strong><small>Browse the current public-content toolkit.</small></span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a><a href="{{ route('blog.index') }}"><i class="ph ph-book-open" aria-hidden="true"></i><span><strong>Read a practical guide</strong><small>Input guidance, context and responsible use.</small></span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a><a href="{{ route('contact') }}"><i class="ph ph-chat-circle" aria-hidden="true"></i><span><strong>Ask our team</strong><small>Send a question or report a lookup problem.</small></span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></aside></div></div></header>
    <div class="sm-section-band"><section class="sm-container sm-faq" aria-labelledby="faq-page-title"><div class="sm-faq-sidebar"><p class="sm-kicker">Common questions</p><h2 id="faq-page-title">A few things worth knowing.</h2><p class="sm-faq-sub">If your question is not covered, tell us which tool and public link you were using.</p><a class="sm-text-link" href="{{ route('contact') }}">Contact support <i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></div><div class="sm-faq-list">
        @forelse($questions as $index => $question)
        <div class="sm-faq-item {{ $index === 0 ? 'is-open' : '' }}"><button class="sm-faq-question" type="button" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="faq-answer-{{ $question->id }}"><span>{{ $question->question }}</span><span class="sm-faq-toggle"><i class="ph ph-plus" aria-hidden="true"></i></span></button><div class="sm-faq-collapse" id="faq-answer-{{ $question->id }}"><div class="sm-faq-body"><p>{{ $question->answer }}</p></div></div></div>
        @empty
        <p class="sm-faq-empty">No questions have been published yet. Contact support if you need help.</p>
        @endforelse
    </div></section></div>
</div>
@endsection
